<?php

require_once __DIR__ . "/../helpers/auth.php";
require_once __DIR__ . "/../helpers/validation.php";
require_once __DIR__ . "/../models/habitacion.php";

requireAuth("admin");
requirePostRequest("admin_habitaciones");

$habitacionModel = new Habitacion();

if (isset($_POST["crear_habitacion"])) {
    requireValidCsrfToken("admin_habitaciones", [], $_POST);

    $nombre = trim((string) ($_POST["nombre"] ?? ""));
    $tipoAlojamiento = validateEnumValue($_POST["tipo_alojamiento"] ?? "", ["completo", "principal", "apartamento"]);
    $capacidad = validatePositiveInt($_POST["capacidad"] ?? null, 1, 30);
    $precio = validatePositiveFloat($_POST["precio"] ?? null, 0, 99999);
    $estado = validateEnumValue($_POST["estado"] ?? "", ["disponible", "ocupada", "mantenimiento"]);

    if ($nombre === "" || !$tipoAlojamiento || !$capacidad || $precio === null || !$estado) {
        flashAndRedirect("admin_habitaciones", "Revisa los datos del alojamiento antes de guardarlo.", "error", [], $_POST);
    }

    if ($habitacionModel->existeNombre($nombre)) {
        flashAndRedirect("admin_habitaciones", "Ya existe un alojamiento con ese nombre.", "error", [], $_POST);
    }

    if (!$habitacionModel->crear($nombre, $tipoAlojamiento, $capacidad, $precio, $estado)) {
        flashAndRedirect("admin_habitaciones", "No se pudo crear el alojamiento.", "error", [], $_POST);
    }

    flashAndRedirect("admin_habitaciones", "Alojamiento creado correctamente.", "success");
}

if (isset($_POST["actualizar_habitacion"])) {
    $idHabitacion = validatePositiveInt($_POST["id_habitacion"] ?? null);
    requireValidCsrfToken("admin_habitaciones", $idHabitacion ? ["editar" => $idHabitacion] : [], $_POST);

    $nombre = trim((string) ($_POST["nombre"] ?? ""));
    $tipoAlojamiento = validateEnumValue($_POST["tipo_alojamiento"] ?? "", ["completo", "principal", "apartamento"]);
    $capacidad = validatePositiveInt($_POST["capacidad"] ?? null, 1, 30);
    $precio = validatePositiveFloat($_POST["precio"] ?? null, 0, 99999);
    $estado = validateEnumValue($_POST["estado"] ?? "", ["disponible", "ocupada", "mantenimiento"]);
    $params = $idHabitacion ? ["editar" => $idHabitacion] : [];

    if (!$idHabitacion || $nombre === "" || !$tipoAlojamiento || !$capacidad || $precio === null || !$estado) {
        flashAndRedirect("admin_habitaciones", "Revisa los datos del alojamiento antes de actualizarlo.", "error", $params, $_POST);
    }

    if (!$habitacionModel->obtenerPorId($idHabitacion)) {
        flashAndRedirect("admin_habitaciones", "El alojamiento que intentas editar no existe.");
    }

    if ($habitacionModel->existeNombre($nombre, $idHabitacion)) {
        flashAndRedirect("admin_habitaciones", "Ya existe otro alojamiento con ese nombre.", "error", $params, $_POST);
    }

    if (!$habitacionModel->actualizar($idHabitacion, $nombre, $tipoAlojamiento, $capacidad, $precio, $estado)) {
        flashAndRedirect("admin_habitaciones", "No se pudo actualizar el alojamiento.", "error", $params, $_POST);
    }

    flashAndRedirect("admin_habitaciones", "Alojamiento actualizado correctamente.", "success");
}

if (isset($_POST["eliminar_habitacion"])) {
    requireValidCsrfToken("admin_habitaciones");

    $idHabitacion = validatePositiveInt($_POST["id_habitacion"] ?? null);

    if (!$idHabitacion) {
        flashAndRedirect("admin_habitaciones", "Alojamiento no valido para eliminar.");
    }

    $habitacion = $habitacionModel->obtenerPorId($idHabitacion);

    if (!$habitacion) {
        flashAndRedirect("admin_habitaciones", "El alojamiento indicado no existe.");
    }

    if (($habitacion["estado"] ?? "") === "ocupada") {
        flashAndRedirect("admin_habitaciones", "No se puede eliminar un alojamiento ocupado.");
    }

    if ($habitacionModel->tieneReservas($idHabitacion)) {
        flashAndRedirect(
            "admin_habitaciones",
            "No se puede eliminar el alojamiento porque tiene reservas asociadas. Cambialo a mantenimiento o desactivalo."
        );
    }

    if (!$habitacionModel->eliminar($idHabitacion)) {
        flashAndRedirect("admin_habitaciones", "No se pudo eliminar el alojamiento.");
    }

    flashAndRedirect("admin_habitaciones", "Alojamiento eliminado correctamente.", "success");
}

flashAndRedirect("admin_habitaciones", "Accion no reconocida.");