<?php

require_once __DIR__ . "/../config/database.php";

class Reserva
{
    public const DURACION_MESA_MINUTOS = 120;
    public const HORA_MESA_INICIO = "13:30";
    public const HORA_MESA_FIN = "16:30";
    public const INTERVALO_MESA_SEGUNDOS = 1800;
    public const ESTADO_PENDIENTE = "pendiente";
    public const ESTADO_CONFIRMADA = "confirmada";
    public const ESTADO_CANCELADA = "cancelada";

    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function obtenerPorUsuario($idUsuario)
    {
        $sql = "SELECT
                    r.id_reserva,
                    r.id_usuario,
                    r.id_habitacion,
                    r.id_mesa,
                    r.tipo_reserva,
                    r.fecha_inicio,
                    r.fecha_fin,
                    r.hora_reserva,
                    r.comensales,
                    r.ubicacion_preferida,
                    r.estado,
                    h.nombre AS nombre_habitacion,
                    m.numero AS numero_mesa
                FROM reservas r
                LEFT JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
                LEFT JOIN mesas m ON r.id_mesa = m.id_mesa
                WHERE r.id_usuario = :id_usuario
                ORDER BY r.fecha_inicio DESC, r.hora_reserva DESC, r.id_reserva DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $this->enriquecerReservas($stmt->fetchAll());
    }

    public function obtenerPorId($idReserva)
    {
        $sql = "SELECT
                r.id_reserva,
                r.id_usuario,
                r.id_habitacion,
                r.id_mesa,
                r.tipo_reserva,
                r.fecha_inicio,
                r.fecha_fin,
                r.hora_reserva,
                r.comensales,
                r.ubicacion_preferida,
                r.estado,
                u.nombre AS nombre_usuario,
                u.email AS email_usuario,
                h.nombre AS nombre_habitacion,
                m.numero AS numero_mesa
            FROM reservas r
            INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
            LEFT JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
            LEFT JOIN mesas m ON r.id_mesa = m.id_mesa
            WHERE r.id_reserva = :id_reserva
            LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_reserva", $idReserva, PDO::PARAM_INT);
        $stmt->execute();

        $reserva = $stmt->fetch();

        return $reserva ? $this->enriquecerReserva($reserva) : null;
    }

    public function contarTodas()
    {
        $sql = "SELECT COUNT(*) FROM reservas";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function contarPorEstado($estado)
    {
        $sql = "SELECT COUNT(*) FROM reservas WHERE estado = :estado";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function obtenerTodasFiltradas($filtroTipo = "", $filtroEstado = "", $busqueda = "")
    {
        $sql = "SELECT
                    r.id_reserva,
                    r.id_usuario,
                    r.tipo_reserva,
                    r.fecha_inicio,
                    r.fecha_fin,
                    r.hora_reserva,
                    r.comensales,
                    r.ubicacion_preferida,
                    r.estado,
                    u.nombre AS nombre_usuario,
                    u.email AS email_usuario,
                    h.nombre AS nombre_habitacion,
                    m.numero AS numero_mesa
                FROM reservas r
                INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                LEFT JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
                LEFT JOIN mesas m ON r.id_mesa = m.id_mesa
                WHERE 1=1";

        $params = [];

        if ($filtroTipo !== "") {
            $sql .= " AND r.tipo_reserva = :tipo";
            $params[":tipo"] = $filtroTipo;
        }

        if ($filtroEstado !== "") {
            $sql .= " AND r.estado = :estado";
            $params[":estado"] = $filtroEstado;
        }

        if ($busqueda !== "") {
            $sql .= " AND (u.nombre LIKE :busqueda OR u.email LIKE :busqueda)";
            $params[":busqueda"] = "%" . $busqueda . "%";
        }

        $sql .= " ORDER BY r.fecha_inicio DESC, r.hora_reserva DESC, r.id_reserva DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->enriquecerReservas($stmt->fetchAll());
    }

    public function obtenerRecientes($limite = 6)
    {
        $limite = max(1, (int) $limite);

        $sql = "SELECT
                    r.id_reserva,
                    r.id_usuario,
                    r.fecha_inicio,
                    r.fecha_fin,
                    r.hora_reserva,
                    r.tipo_reserva,
                    r.estado,
                    r.comensales,
                    u.nombre AS nombre_usuario
                FROM reservas r
                INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                ORDER BY r.id_reserva DESC
                LIMIT " . $limite;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $this->enriquecerReservas($stmt->fetchAll());
    }

    public function haySolapeHabitacion($idHabitacion, $fechaInicio, $fechaFin)
    {
        $sqlTipo = "SELECT tipo_alojamiento
                FROM habitaciones
                WHERE id_habitacion = :id_habitacion
                LIMIT 1";

        $stmtTipo = $this->db->prepare($sqlTipo);
        $stmtTipo->bindParam(":id_habitacion", $idHabitacion, PDO::PARAM_INT);
        $stmtTipo->execute();

        $habitacion = $stmtTipo->fetch(PDO::FETCH_ASSOC);

        if (!$habitacion) {
            return true;
        }

        $tipoAlojamiento = $habitacion["tipo_alojamiento"] ?? "";

        if ($tipoAlojamiento === "completo") {
            $sql = "SELECT COUNT(*)
                FROM reservas r
                INNER JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
                WHERE r.tipo_reserva = 'habitacion'
                  AND (r.estado = :estado_pendiente OR r.estado = :estado_confirmada)
                  AND r.fecha_inicio < :fecha_fin
                  AND r.fecha_fin > :fecha_inicio";

            $stmt = $this->db->prepare($sql);
            $estadoPendiente = self::ESTADO_PENDIENTE;
            $estadoConfirmada = self::ESTADO_CONFIRMADA;

            $stmt->bindParam(":estado_pendiente", $estadoPendiente);
            $stmt->bindParam(":estado_confirmada", $estadoConfirmada);
            $stmt->bindParam(":fecha_inicio", $fechaInicio);
            $stmt->bindParam(":fecha_fin", $fechaFin);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        }

        $sql = "SELECT COUNT(*)
            FROM reservas r
            INNER JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
            WHERE r.tipo_reserva = 'habitacion'
              AND (r.estado = :estado_pendiente OR r.estado = :estado_confirmada)
              AND r.fecha_inicio < :fecha_fin
              AND r.fecha_fin > :fecha_inicio
              AND (
                    r.id_habitacion = :id_habitacion
                    OR h.tipo_alojamiento = :tipo_completo
              )";

        $stmt = $this->db->prepare($sql);
        $estadoPendiente = self::ESTADO_PENDIENTE;
        $estadoConfirmada = self::ESTADO_CONFIRMADA;
        $tipoCompleto = "completo";

        $stmt->bindParam(":id_habitacion", $idHabitacion, PDO::PARAM_INT);
        $stmt->bindParam(":estado_pendiente", $estadoPendiente);
        $stmt->bindParam(":estado_confirmada", $estadoConfirmada);
        $stmt->bindParam(":tipo_completo", $tipoCompleto);
        $stmt->bindParam(":fecha_inicio", $fechaInicio);
        $stmt->bindParam(":fecha_fin", $fechaFin);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    public function crearReservaHabitacion($idUsuario, $idHabitacion, $fechaInicio, $fechaFin)
    {
        $sql = "INSERT INTO reservas
                (id_usuario, id_habitacion, id_mesa, tipo_reserva, fecha_inicio, fecha_fin, estado, hora_reserva, comensales, ubicacion_preferida)
                VALUES
                (:id_usuario, :id_habitacion, NULL, 'habitacion', :fecha_inicio, :fecha_fin, :estado, NULL, NULL, NULL)";

        $stmt = $this->db->prepare($sql);
        $estadoPendiente = self::ESTADO_PENDIENTE;
        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->bindParam(":id_habitacion", $idHabitacion, PDO::PARAM_INT);
        $stmt->bindParam(":fecha_inicio", $fechaInicio);
        $stmt->bindParam(":fecha_fin", $fechaFin);
        $stmt->bindParam(":estado", $estadoPendiente);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function crearReservaMesa($idUsuario, $idMesa, $fechaMesa, $horaMesa, $comensales, $ubicacionPreferida)
    {
        $sql = "INSERT INTO reservas
                (id_usuario, id_habitacion, id_mesa, tipo_reserva, fecha_inicio, fecha_fin, estado, hora_reserva, comensales, ubicacion_preferida)
                VALUES
                (:id_usuario, NULL, :id_mesa, 'mesa', :fecha_inicio, :fecha_fin, :estado, :hora_reserva, :comensales, :ubicacion_preferida)";

        $stmt = $this->db->prepare($sql);
        $estadoPendiente = self::ESTADO_PENDIENTE;
        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->bindParam(":id_mesa", $idMesa, PDO::PARAM_INT);
        $stmt->bindParam(":fecha_inicio", $fechaMesa);
        $stmt->bindParam(":fecha_fin", $fechaMesa);
        $stmt->bindParam(":estado", $estadoPendiente);
        $stmt->bindParam(":hora_reserva", $horaMesa);
        $stmt->bindParam(":comensales", $comensales, PDO::PARAM_INT);
        $stmt->bindParam(":ubicacion_preferida", $ubicacionPreferida);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function reasignarMesa($idReserva, $idMesa)
    {
        $sql = "UPDATE reservas
                SET id_mesa = :id_mesa
                WHERE id_reserva = :id_reserva
                  AND tipo_reserva = 'mesa'";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_mesa", $idMesa, PDO::PARAM_INT);
        $stmt->bindParam(":id_reserva", $idReserva, PDO::PARAM_INT);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function cancelarPorUsuario($idReserva, $idUsuario)
    {
        $sql = "UPDATE reservas
                SET estado = :estado_cancelada
                WHERE id_reserva = :id_reserva
                  AND id_usuario = :id_usuario
                  AND estado = :estado_confirmada";

        $stmt = $this->db->prepare($sql);
        $estadoCancelada = self::ESTADO_CANCELADA;
        $estadoConfirmada = self::ESTADO_CONFIRMADA;
        $stmt->bindParam(":estado_cancelada", $estadoCancelada);
        $stmt->bindParam(":id_reserva", $idReserva, PDO::PARAM_INT);
        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->bindParam(":estado_confirmada", $estadoConfirmada);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function cancelarComoAdmin($idReserva)
    {
        $sql = "UPDATE reservas
                SET estado = :estado_cancelada
                WHERE id_reserva = :id_reserva
                  AND estado = :estado_confirmada";

        $stmt = $this->db->prepare($sql);
        $estadoCancelada = self::ESTADO_CANCELADA;
        $estadoConfirmada = self::ESTADO_CONFIRMADA;
        $stmt->bindParam(":estado_cancelada", $estadoCancelada);
        $stmt->bindParam(":id_reserva", $idReserva, PDO::PARAM_INT);
        $stmt->bindParam(":estado_confirmada", $estadoConfirmada);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function confirmarComoAdmin($idReserva)
    {
        $sql = "UPDATE reservas
            SET estado = :estado_confirmada
            WHERE id_reserva = :id_reserva
              AND estado = :estado_pendiente";

        $stmt = $this->db->prepare($sql);
        $estadoConfirmada = self::ESTADO_CONFIRMADA;
        $estadoPendiente = self::ESTADO_PENDIENTE;
        $stmt->bindParam(":estado_confirmada", $estadoConfirmada);
        $stmt->bindParam(":id_reserva", $idReserva, PDO::PARAM_INT);
        $stmt->bindParam(":estado_pendiente", $estadoPendiente);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function cancelarPendienteComoAdmin($idReserva)
    {
        $sql = "UPDATE reservas
            SET estado = :estado_cancelada
            WHERE id_reserva = :id_reserva
              AND estado = :estado_pendiente";

        $stmt = $this->db->prepare($sql);
        $estadoCancelada = self::ESTADO_CANCELADA;
        $estadoPendiente = self::ESTADO_PENDIENTE;
        $stmt->bindParam(":estado_cancelada", $estadoCancelada);
        $stmt->bindParam(":id_reserva", $idReserva, PDO::PARAM_INT);
        $stmt->bindParam(":estado_pendiente", $estadoPendiente);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function obtenerPorFecha(string $fecha)
    {
        $sql = "SELECT
                r.id_reserva,
                r.id_usuario,
                r.tipo_reserva,
                r.fecha_inicio,
                r.fecha_fin,
                r.hora_reserva,
                r.comensales,
                r.ubicacion_preferida,
                r.estado,
                u.nombre AS nombre_usuario,
                u.email AS email_usuario,
                h.nombre AS nombre_habitacion,
                m.numero AS numero_mesa
            FROM reservas r
            INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
            LEFT JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
            LEFT JOIN mesas m ON r.id_mesa = m.id_mesa
            WHERE (
                (r.tipo_reserva = 'mesa' AND r.fecha_inicio = :fecha_mesa)
                OR
                (r.tipo_reserva = 'habitacion' AND r.fecha_inicio <= :fecha_inicio_hab AND r.fecha_fin > :fecha_fin_hab)
            )
            ORDER BY r.hora_reserva ASC, r.id_reserva DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":fecha_mesa" => $fecha,
            ":fecha_inicio_hab" => $fecha,
            ":fecha_fin_hab" => $fecha,
        ]);

        return $this->enriquecerReservas($stmt->fetchAll());
    }

    public function obtenerMesasPorFecha(string $fecha)
    {
        $sql = "SELECT DISTINCT
                m.id_mesa,
                m.numero,
                m.capacidad,
                m.ubicacion,
                r.hora_reserva,
                r.estado,
                u.nombre AS nombre_usuario
            FROM reservas r
            INNER JOIN mesas m ON r.id_mesa = m.id_mesa
            INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
            WHERE r.tipo_reserva = 'mesa'
              AND r.fecha_inicio = :fecha
            ORDER BY m.numero ASC, r.hora_reserva ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":fecha" => $fecha,
        ]);

        return $stmt->fetchAll();
    }

    public function obtenerHabitacionesPorFecha(string $fecha)
    {
        $sql = "SELECT DISTINCT
                h.id_habitacion,
                h.nombre,
                h.tipo_alojamiento,
                h.capacidad,
                h.precio,
                r.estado,
                u.nombre AS nombre_usuario,
                r.fecha_inicio,
                r.fecha_fin
            FROM reservas r
            INNER JOIN habitaciones h ON r.id_habitacion = h.id_habitacion
            INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
            WHERE r.tipo_reserva = 'habitacion'
              AND r.fecha_inicio <= :fecha_inicio_hab
              AND r.fecha_fin > :fecha_fin_hab
            ORDER BY
                CASE
                    WHEN h.tipo_alojamiento = 'completo' THEN 1
                    WHEN h.tipo_alojamiento = 'principal' THEN 2
                    WHEN h.tipo_alojamiento = 'apartamento' THEN 3
                    ELSE 4
                END,
                h.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":fecha_inicio_hab" => $fecha,
            ":fecha_fin_hab" => $fecha,
        ]);

        return $stmt->fetchAll();
    }

    private function enriquecerReservas($reservas)
    {
        $resultado = [];

        foreach ($reservas as $reserva) {
            $resultado[] = $this->enriquecerReserva($reserva);
        }

        return $resultado;
    }

    private function enriquecerReserva($reserva)
    {
        $ahora = new DateTimeImmutable("now");
        $inicio = $this->obtenerInicioReserva($reserva);
        $fin = $this->obtenerFinReserva($reserva, $inicio);

        $reserva["detalle_reserva"] = $this->obtenerDetalleReserva($reserva);
        $reserva["detalle_reserva_publico"] = $this->obtenerDetalleReservaPublico($reserva);
        $reserva["duracion_mesa_minutos"] = self::DURACION_MESA_MINUTOS;
        $reserva["cancelable_cliente"] = false;
        $reserva["cancelable_admin"] = false;

        $estadoReal = $reserva["estado"] ?? "";

        if ($estadoReal === self::ESTADO_CANCELADA) {
            $reserva["estado_mostrado"] = "Cancelada";
            $reserva["estado_codigo"] = "cancelada";
            $reserva["estado_css"] = "estado-cancelada";

            return $reserva;
        }

        if ($estadoReal === self::ESTADO_PENDIENTE) {
            $reserva["estado_mostrado"] = "Pendiente";
            $reserva["estado_codigo"] = "pendiente";
            $reserva["estado_css"] = "estado-pendiente";
            $reserva["cancelable_admin"] = true;

            return $reserva;
        }

        if ($estadoReal === self::ESTADO_CONFIRMADA) {
            if ($fin <= $ahora) {
                $reserva["estado_mostrado"] = "Finalizada";
                $reserva["estado_codigo"] = "finalizada";
                $reserva["estado_css"] = "estado-finalizada";

                return $reserva;
            }

            if ($inicio <= $ahora) {
                $reserva["estado_mostrado"] = "En curso";
                $reserva["estado_codigo"] = "en_curso";
                $reserva["estado_css"] = "estado-activa";
                $reserva["cancelable_admin"] = true;

                return $reserva;
            }

            $reserva["estado_mostrado"] = "Confirmada";
            $reserva["estado_codigo"] = "confirmada";
            $reserva["estado_css"] = "estado-confirmada";
            $reserva["cancelable_cliente"] = true;
            $reserva["cancelable_admin"] = true;

            return $reserva;
        }

        $reserva["estado_mostrado"] = "Desconocido";
        $reserva["estado_codigo"] = "desconocido";
        $reserva["estado_css"] = "estado-pendiente";

        return $reserva;
    }

    private function obtenerInicioReserva($reserva)
    {
        $fechaInicio = $reserva["fecha_inicio"] ?? date("Y-m-d");

        if (($reserva["tipo_reserva"] ?? "") === "mesa") {
            $horaReserva = $reserva["hora_reserva"] ?? "00:00";
            return new DateTimeImmutable($fechaInicio . " " . $horaReserva);
        }

        return new DateTimeImmutable($fechaInicio . " 00:00");
    }

    private function obtenerFinReserva($reserva, DateTimeImmutable $inicio)
    {
        if (($reserva["tipo_reserva"] ?? "") === "mesa") {
            return $inicio->modify("+" . self::DURACION_MESA_MINUTOS . " minutes");
        }

        $fechaFin = $reserva["fecha_fin"] ?? $reserva["fecha_inicio"] ?? date("Y-m-d");
        return new DateTimeImmutable($fechaFin . " 00:00");
    }

    private function obtenerDetalleReserva($reserva)
    {
        if (($reserva["tipo_reserva"] ?? "") === "habitacion") {
            return $reserva["nombre_habitacion"] ?? "Alojamiento";
        }

        return "Mesa " . ($reserva["numero_mesa"] ?? "-");
    }

    private function obtenerDetalleReservaPublico($reserva)
    {
        if (($reserva["tipo_reserva"] ?? "") === "habitacion") {
            return $reserva["nombre_habitacion"] ?? "Alojamiento";
        }

        $comensales = $reserva["comensales"] ?? "-";
        $fecha = $reserva["fecha_inicio"] ?? date("Y-m-d");
        $hora = $reserva["hora_reserva"] ?? "--:--";

        return "Mesa para " . $comensales . " personas el " . $fecha . " a las " . $hora;
    }
}
