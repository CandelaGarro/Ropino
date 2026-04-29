<?php

require_once __DIR__ . "/../config/database.php";

class Mesa
{
    private const DURACION_RESERVA_MINUTOS = 120;
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function obtenerTodas()
    {
        $sql = "SELECT * FROM mesas ORDER BY numero ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($idMesa)
    {
        $sql = "SELECT * FROM mesas WHERE id_mesa = :id_mesa LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_mesa", $idMesa, PDO::PARAM_INT);
        $stmt->execute();

        $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

        return $mesa ?: null;
    }

    public function existeNumero($numero, $idMesaExcluir = null)
    {
        $sql = "SELECT id_mesa
                FROM mesas
                WHERE numero = :numero";

        if ($idMesaExcluir !== null) {
            $sql .= " AND id_mesa != :id_mesa";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":numero", $numero, PDO::PARAM_INT);

        if ($idMesaExcluir !== null) {
            $stmt->bindParam(":id_mesa", $idMesaExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($numero, $capacidad, $ubicacion, $estado)
    {
        $sql = "INSERT INTO mesas (numero, capacidad, ubicacion, estado)
                VALUES (:numero, :capacidad, :ubicacion, :estado)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":numero", $numero, PDO::PARAM_INT);
        $stmt->bindParam(":capacidad", $capacidad, PDO::PARAM_INT);
        $stmt->bindParam(":ubicacion", $ubicacion);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    public function actualizar($idMesa, $numero, $capacidad, $ubicacion, $estado)
    {
        $sql = "UPDATE mesas
                SET numero = :numero,
                    capacidad = :capacidad,
                    ubicacion = :ubicacion,
                    estado = :estado
                WHERE id_mesa = :id_mesa";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":numero", $numero, PDO::PARAM_INT);
        $stmt->bindParam(":capacidad", $capacidad, PDO::PARAM_INT);
        $stmt->bindParam(":ubicacion", $ubicacion);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_mesa", $idMesa, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function tieneReservas($idMesa)
    {
        $sql = "SELECT COUNT(*) FROM reservas WHERE id_mesa = :id_mesa";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_mesa", $idMesa, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    public function eliminar($idMesa)
    {
        $sql = "DELETE FROM mesas WHERE id_mesa = :id_mesa";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_mesa", $idMesa, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function contarTotal()
    {
        $sql = "SELECT COUNT(*) FROM mesas";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function contarPorEstado($estado)
    {
        $sql = "SELECT COUNT(*) FROM mesas WHERE estado = :estado";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function buscarDisponible(
        $comensales,
        $ubicacion,
        $fechaReserva,
        $horaReserva,
        $estadosBloqueantes = ["pendiente", "confirmada"],
        $idReservaExcluir = null
    )
    {
        $estadosBloqueantes = array_values(array_unique(array_filter(array_map("strval", (array) $estadosBloqueantes))));

        if (empty($estadosBloqueantes)) {
            $estadosBloqueantes = ["pendiente", "confirmada"];
        }

        $placeholdersEstado = [];
        foreach ($estadosBloqueantes as $indice => $estado) {
            $placeholdersEstado[] = ":estado_" . $indice;
        }

        $filtroExclusionReserva = "";
        if ($idReservaExcluir !== null) {
            $filtroExclusionReserva = " AND r.id_reserva != :id_reserva_excluir";
        }

        $sql = "SELECT m.id_mesa, m.numero
                FROM mesas m
                WHERE m.capacidad >= :comensales
                  AND m.ubicacion = :ubicacion
                  AND m.estado = 'disponible'
                  AND NOT EXISTS (
                      SELECT 1
                      FROM reservas r
                      WHERE r.tipo_reserva = 'mesa'
                        AND r.estado IN (" . implode(", ", $placeholdersEstado) . ")
                        AND r.id_mesa = m.id_mesa
                        " . $filtroExclusionReserva . "
                        AND TIMESTAMP(r.fecha_inicio, r.hora_reserva) < DATE_ADD(
                            TIMESTAMP(:fecha_reserva_inicio, :hora_reserva_inicio),
                            INTERVAL " . self::DURACION_RESERVA_MINUTOS . " MINUTE
                        )
                        AND DATE_ADD(
                            TIMESTAMP(r.fecha_inicio, r.hora_reserva),
                            INTERVAL " . self::DURACION_RESERVA_MINUTOS . " MINUTE
                        ) > TIMESTAMP(:fecha_reserva_inicio_comparacion, :hora_reserva_inicio_comparacion)
                  )
                ORDER BY m.capacidad ASC, m.numero ASC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":comensales", $comensales, PDO::PARAM_INT);
        $stmt->bindParam(":ubicacion", $ubicacion);
        $stmt->bindParam(":fecha_reserva_inicio", $fechaReserva);
        $stmt->bindParam(":hora_reserva_inicio", $horaReserva);
        $stmt->bindParam(":fecha_reserva_inicio_comparacion", $fechaReserva);
        $stmt->bindParam(":hora_reserva_inicio_comparacion", $horaReserva);

        foreach ($estadosBloqueantes as $indice => $estado) {
            $stmt->bindValue(":estado_" . $indice, $estado);
        }

        if ($idReservaExcluir !== null) {
            $stmt->bindValue(":id_reserva_excluir", (int) $idReservaExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();

        $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

        return $mesa ?: null;
    }

    public function estaDisponibleParaReserva(
        $idMesa,
        $fechaReserva,
        $horaReserva,
        $estadosBloqueantes = ["pendiente", "confirmada"],
        $idReservaExcluir = null
    )
    {
        $estadosBloqueantes = array_values(array_unique(array_filter(array_map("strval", (array) $estadosBloqueantes))));

        if (empty($estadosBloqueantes)) {
            $estadosBloqueantes = ["pendiente", "confirmada"];
        }

        $placeholdersEstado = [];
        foreach ($estadosBloqueantes as $indice => $estado) {
            $placeholdersEstado[] = ":estado_" . $indice;
        }

        $filtroExclusionReserva = "";
        if ($idReservaExcluir !== null) {
            $filtroExclusionReserva = " AND r.id_reserva != :id_reserva_excluir";
        }

        $sql = "SELECT COUNT(*)
                FROM reservas r
                WHERE r.tipo_reserva = 'mesa'
                  AND r.id_mesa = :id_mesa
                  AND r.estado IN (" . implode(", ", $placeholdersEstado) . ")
                  " . $filtroExclusionReserva . "
                  AND TIMESTAMP(r.fecha_inicio, r.hora_reserva) < DATE_ADD(
                      TIMESTAMP(:fecha_reserva_inicio, :hora_reserva_inicio),
                      INTERVAL " . self::DURACION_RESERVA_MINUTOS . " MINUTE
                  )
                  AND DATE_ADD(
                      TIMESTAMP(r.fecha_inicio, r.hora_reserva),
                      INTERVAL " . self::DURACION_RESERVA_MINUTOS . " MINUTE
                  ) > TIMESTAMP(:fecha_reserva_inicio_comparacion, :hora_reserva_inicio_comparacion)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id_mesa", (int) $idMesa, PDO::PARAM_INT);
        $stmt->bindParam(":fecha_reserva_inicio", $fechaReserva);
        $stmt->bindParam(":hora_reserva_inicio", $horaReserva);
        $stmt->bindParam(":fecha_reserva_inicio_comparacion", $fechaReserva);
        $stmt->bindParam(":hora_reserva_inicio_comparacion", $horaReserva);

        foreach ($estadosBloqueantes as $indice => $estado) {
            $stmt->bindValue(":estado_" . $indice, $estado);
        }

        if ($idReservaExcluir !== null) {
            $stmt->bindValue(":id_reserva_excluir", (int) $idReservaExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn() === 0;
    }
}
