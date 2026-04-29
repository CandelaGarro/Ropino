<?php

require_once __DIR__ . "/../config/database.php";

class Habitacion
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function obtenerTodas()
    {
        $sql = "SELECT *
                FROM habitaciones
                ORDER BY
                    CASE
                        WHEN tipo_alojamiento = 'completo' THEN 1
                        WHEN tipo_alojamiento = 'principal' THEN 2
                        WHEN tipo_alojamiento = 'apartamento' THEN 3
                        ELSE 4
                    END,
                    nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDisponiblesParaReserva()
    {
        $sql = "SELECT *
                FROM habitaciones
                WHERE estado = 'disponible'
                ORDER BY
                    CASE
                        WHEN tipo_alojamiento = 'completo' THEN 1
                        WHEN tipo_alojamiento = 'principal' THEN 2
                        WHEN tipo_alojamiento = 'apartamento' THEN 3
                        ELSE 4
                    END,
                    nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($idHabitacion)
    {
        $sql = "SELECT * 
                FROM habitaciones 
                WHERE id_habitacion = :id_habitacion 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_habitacion", $idHabitacion, PDO::PARAM_INT);
        $stmt->execute();

        $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

        return $habitacion ?: null;
    }

    public function existeNombre($nombre, $idHabitacionExcluir = null)
    {
        $sql = "SELECT id_habitacion
                FROM habitaciones
                WHERE nombre = :nombre";

        if ($idHabitacionExcluir !== null) {
            $sql .= " AND id_habitacion != :id_habitacion";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);

        if ($idHabitacionExcluir !== null) {
            $stmt->bindParam(":id_habitacion", $idHabitacionExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $tipoAlojamiento, $capacidad, $precio, $estado)
    {
        $sql = "INSERT INTO habitaciones (nombre, tipo_alojamiento, capacidad, precio, estado)
                VALUES (:nombre, :tipo_alojamiento, :capacidad, :precio, :estado)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":tipo_alojamiento", $tipoAlojamiento);
        $stmt->bindParam(":capacidad", $capacidad, PDO::PARAM_INT);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    public function actualizar($idHabitacion, $nombre, $tipoAlojamiento, $capacidad, $precio, $estado)
    {
        $sql = "UPDATE habitaciones
                SET nombre = :nombre,
                    tipo_alojamiento = :tipo_alojamiento,
                    capacidad = :capacidad,
                    precio = :precio,
                    estado = :estado
                WHERE id_habitacion = :id_habitacion";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":tipo_alojamiento", $tipoAlojamiento);
        $stmt->bindParam(":capacidad", $capacidad, PDO::PARAM_INT);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_habitacion", $idHabitacion, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function tieneReservas($idHabitacion)
    {
        $sql = "SELECT COUNT(*) 
                FROM reservas 
                WHERE id_habitacion = :id_habitacion";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_habitacion", $idHabitacion, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    public function eliminar($idHabitacion)
    {
        $sql = "DELETE FROM habitaciones 
                WHERE id_habitacion = :id_habitacion";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_habitacion", $idHabitacion, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function contarTotal()
    {
        $sql = "SELECT COUNT(*) FROM habitaciones";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function contarPorEstado($estado)
    {
        $sql = "SELECT COUNT(*) 
                FROM habitaciones 
                WHERE estado = :estado";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}