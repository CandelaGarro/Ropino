<?php

class Database
{
    private $host = "127.0.0.1";
    private $db_name = "ropino";
    private $username = "root";
    private $password = "";

    public function connect()
    {
        try {
            return new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5,
                ]
            );
        } catch (PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            exit("No se pudo conectar con la base de datos.");
        }
    }
}
