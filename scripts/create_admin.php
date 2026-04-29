<?php

if (PHP_SAPI !== "cli") {
    http_response_code(403);
    exit("Este script solo se puede ejecutar desde consola." . PHP_EOL);
}

require_once dirname(__DIR__) . "/src/config/database.php";

$options = getopt("", ["nombre:", "email:", "password:", "rol::"]);

$nombre = trim((string) ($options["nombre"] ?? ""));
$email = trim((string) ($options["email"] ?? ""));
$password = (string) ($options["password"] ?? "");
$rol = trim((string) ($options["rol"] ?? "admin"));

if ($nombre === "" || $email === "" || $password === "") {
    fwrite(STDERR, "Uso: php scripts/create_admin.php --nombre=\"Admin\" --email=\"admin@ropino.com\" --password=\"TuClaveSegura\" [--rol=\"admin\"]" . PHP_EOL);
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "El email indicado no es valido." . PHP_EOL);
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "La contrasena debe tener al menos 8 caracteres." . PHP_EOL);
    exit(1);
}

if ($rol !== "admin") {
    fwrite(STDERR, "Este script solo permite crear usuarios con rol admin." . PHP_EOL);
    exit(1);
}

$database = new Database();
$db = $database->connect();

$check = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1");
$check->bindValue(":email", $email);
$check->execute();

if ($check->fetch()) {
    fwrite(STDERR, "Ya existe un usuario con ese email." . PHP_EOL);
    exit(1);
}

$sql = "INSERT INTO usuarios (nombre, email, password, rol)
        VALUES (:nombre, :email, :password, :rol)";

$stmt = $db->prepare($sql);
$stmt->bindValue(":nombre", $nombre);
$stmt->bindValue(":email", $email);
$stmt->bindValue(":password", password_hash($password, PASSWORD_DEFAULT));
$stmt->bindValue(":rol", $rol);

if (!$stmt->execute()) {
    fwrite(STDERR, "No se pudo crear el admin." . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Admin creado correctamente para {$email}" . PHP_EOL);
