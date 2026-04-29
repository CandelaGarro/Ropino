<?php

require_once __DIR__ . "/../helpers/auth.php";
require_once __DIR__ . "/../helpers/validation.php";
require_once __DIR__ . "/../models/usuario.php";

$paginaOrigen = isset($_POST["cambiar_password"]) ? "configuracion" : "mis_datos";
$usuarioActual = requireAuth();
requirePostRequest($paginaOrigen);

$usuarioModel = new Usuario();

if (isset($_POST["actualizar_datos"])) {
    requireValidCsrfToken("mis_datos", [], $_POST);

    $nombre = validateText($_POST["nombre"] ?? "", 2, 80);
    $email = validateEmailAddress($_POST["email"] ?? "");

    if (!$nombre || !$email) {
        flashAndRedirect("mis_datos", "Revisa el nombre y el correo electronico.", "error", [], $_POST);
    }

    if ($usuarioModel->existeEmailEnOtroUsuario($email, (int) $usuarioActual["id_usuario"])) {
        flashAndRedirect("mis_datos", "Ese correo ya esta siendo usado por otra cuenta.", "error", [], $_POST);
    }

    if (
        !$usuarioModel->actualizarPerfil(
            (int) $usuarioActual["id_usuario"],
            $nombre,
            $email
        )
    ) {
        flashAndRedirect("mis_datos", "No se pudieron actualizar tus datos.");
    }

    updateSessionUserData([
        "id_usuario" => (int) $usuarioActual["id_usuario"],
        "nombre" => $nombre,
        "email" => $email,
        "rol" => $usuarioActual["rol"] ?? "",
    ]);

    flashAndRedirect("mis_datos", "Tus datos se han actualizado correctamente.", "success");
}

if (isset($_POST["cambiar_password"])) {
    requireValidCsrfToken("configuracion");

    $passwordActual = (string) ($_POST["password_actual"] ?? "");
    $passwordNueva = (string) ($_POST["password_nueva"] ?? "");
    $passwordConfirmacion = (string) ($_POST["password_confirmacion"] ?? "");

    if ($passwordActual === "" || $passwordNueva === "" || $passwordConfirmacion === "") {
        flashAndRedirect("configuracion", "Completa los tres campos de contrasena.");
    }

    if (strlen($passwordNueva) < 8) {
        flashAndRedirect("configuracion", "La nueva contrasena debe tener al menos 8 caracteres.");
    }

    if ($passwordNueva !== $passwordConfirmacion) {
        flashAndRedirect("configuracion", "La confirmacion de la contrasena no coincide.");
    }

    $usuario = $usuarioModel->obtenerPorId((int) $usuarioActual["id_usuario"]);

    if (!$usuario || !password_verify($passwordActual, $usuario["password"])) {
        flashAndRedirect("configuracion", "La contrasena actual no es correcta.");
    }

    if (password_verify($passwordNueva, $usuario["password"])) {
        flashAndRedirect("configuracion", "La nueva contrasena debe ser diferente de la actual.");
    }

    $passwordHash = password_hash($passwordNueva, PASSWORD_DEFAULT);

    if (!$usuarioModel->actualizarPassword((int) $usuarioActual["id_usuario"], $passwordHash)) {
        flashAndRedirect("configuracion", "No se pudo actualizar la contrasena.");
    }

    flashAndRedirect("configuracion", "Contrasena actualizada correctamente.", "success");
}

flashAndRedirect($paginaOrigen, "Accion no reconocida.");
