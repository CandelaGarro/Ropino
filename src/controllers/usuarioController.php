<?php

require_once __DIR__ . "/../helpers/auth.php";
require_once __DIR__ . "/../helpers/validation.php";
require_once __DIR__ . "/../models/usuario.php";

requirePostRequest("login");

$usuarioModel = new Usuario();

if (isset($_POST["registro"])) {
    requireValidCsrfToken("registro", [], $_POST);

    $nombre = validateText($_POST["nombre"] ?? "", 2, 80);
    $email = validateEmailAddress($_POST["email"] ?? "");
    $password = (string) ($_POST["password"] ?? "");

    if (!$nombre || !$email || $password === "") {
        flashAndRedirect("registro", "Todos los campos son obligatorios y deben ser validos.", "error", [], $_POST);
    }

    if (strlen($password) < 8) {
        flashAndRedirect("registro", "La contrasena debe tener al menos 8 caracteres.", "error", [], $_POST);
    }

    if ($usuarioModel->emailExiste($email)) {
        flashAndRedirect("registro", "Ese correo ya esta registrado.", "error", [], $_POST);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if (!$usuarioModel->crear($nombre, $email, $passwordHash)) {
        flashAndRedirect("registro", "No se pudo crear la cuenta. Intentalo de nuevo.");
    }

    flashAndRedirect("login", "Cuenta creada correctamente. Ya puedes iniciar sesion.", "success");
}

if (isset($_POST["login"])) {
    requireValidCsrfToken("login", [], ["email" => $_POST["email"] ?? ""]);

    $email = validateEmailAddress($_POST["email"] ?? "");
    $password = (string) ($_POST["password"] ?? "");

    if (!$email || $password === "") {
        flashAndRedirect("login", "Introduce un correo valido y tu contrasena.", "error", [], ["email" => $_POST["email"] ?? ""]);
    }

    $usuario = $usuarioModel->obtenerPorEmail($email);

    if (!$usuario || !password_verify($password, $usuario["password"])) {
        flashAndRedirect("login", "Correo o contrasena incorrectos.", "error", [], ["email" => $email]);
    }

    clearOldInput();
    loginUser($usuario);
    redirectByRole($usuario);
}

flashAndRedirect("login", "Accion no reconocida.");
