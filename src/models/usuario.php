<?php

require_once __DIR__ . "/../config/database.php";

class Usuario
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function obtenerPorEmail($email)
    {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    public function obtenerPorId($idUsuario)
    {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    public function emailExiste($email)
    {
        return $this->obtenerPorEmail($email) !== null;
    }

    public function existeEmailEnOtroUsuario($email, $idUsuario)
    {
        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE email = :email
                  AND id_usuario != :id_usuario
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $email, $passwordHash)
    {
        $sql = "INSERT INTO usuarios (nombre, email, password)
                VALUES (:nombre, :email, :password)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $passwordHash);

        return $stmt->execute();
    }

    public function actualizarPerfil($idUsuario, $nombre, $email)
    {
        $sql = "UPDATE usuarios
                SET nombre = :nombre,
                    email = :email
                WHERE id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function actualizarPassword($idUsuario, $passwordHash)
    {
        $sql = "UPDATE usuarios
                SET password = :password
                WHERE id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":password", $passwordHash);
        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function contarClientes()
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
