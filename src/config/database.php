<?php

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;

    public function __construct()
    {
        $this->host = defined("DB_HOST") ? DB_HOST : "127.0.0.1";
        $this->db_name = defined("DB_NAME") ? DB_NAME : "ropino";
        $this->username = defined("DB_USERNAME") ? DB_USERNAME : "root";
        $this->password = defined("DB_PASSWORD") ? DB_PASSWORD : "";
    }

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
