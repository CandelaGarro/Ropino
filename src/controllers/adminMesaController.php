<?php

require_once __DIR__ . "/../helpers/auth.php";
require_once __DIR__ . "/../helpers/validation.php";
require_once __DIR__ . "/../models/mesa.php";

requireAuth("admin");
requirePostRequest("admin_mesas");

$mesaModel = new Mesa();

if (isset($_POST["crear_mesa"])) {
    requireValidCsrfToken("admin_mesas", [], $_POST);

    $nombre = validatePositiveInt($_POST["numero"] ?? null, 1, 9999);
    $capacidad = validatePositiveInt($_POST["capacidad"] ?? null, 1, 20);
    $ubicacion = validateEnumValue($_POST["ubicacion"] ?? "", ["interior", "terraza"]);
    $estado = validateEnumValue($_POST["estado"] ?? "", ["disponible", "ocupada"]);

    if (!$nombre || !$capacidad || !$ubicacion || !$estado) {
        flashAndRedirect("admin_mesas", "Revisa los datos de la mesa antes de guardarla.", "error", [], $_POST);
    }

    if ($mesaModel->existeNumero($nombre)) {
        flashAndRedirect("admin_mesas", "Ya existe una mesa con ese nombre.", "error", [], $_POST);
    }

    if (!$mesaModel->crear($nombre, $capacidad, $ubicacion, $estado)) {
        flashAndRedirect("admin_mesas", "No se pudo crear la mesa.", "error", [], $_POST);
    }

    flashAndRedirect("admin_mesas", "Mesa creada correctamente.", "success");
}

if (isset($_POST["editar_mesa"])) {
    $idMesa = validatePositiveInt($_POST["id_mesa"] ?? null);
    requireValidCsrfToken("admin_mesas", $idMesa ? ["editar" => $idMesa] : [], $_POST);

    $nombre = validatePositiveInt($_POST["nombre"] ?? null, 1, 9999);
    $capacidad = validatePositiveInt($_POST["capacidad"] ?? null, 1, 20);
    $ubicacion = validateEnumValue($_POST["ubicacion"] ?? "", ["interior", "terraza"]);
    $estado = validateEnumValue($_POST["estado"] ?? "", ["disponible", "ocupada"]);
    $params = $idMesa ? ["editar" => $idMesa] : [];

    if (!$idMesa || !$nombre || !$capacidad || !$ubicacion || !$estado) {
        flashAndRedirect("admin_mesas", "Revisa los datos de la mesa antes de actualizarla.", "error", $params, $_POST);
    }

    if (!$mesaModel->obtenerPorId($idMesa)) {
        flashAndRedirect("admin_mesas", "La mesa que intentas editar no existe.");
    }

    if ($mesaModel->existeNumero($nombre, $idMesa)) {
        flashAndRedirect("admin_mesas", "Ya existe otra mesa con ese nombre.", "error", $params, $_POST);
    }

    if (!$mesaModel->actualizar($idMesa, $nombre, $capacidad, $ubicacion, $estado)) {
        flashAndRedirect("admin_mesas", "No se pudo actualizar la mesa.", "error", $params, $_POST);
    }

    flashAndRedirect("admin_mesas", "Mesa actualizada correctamente.", "success");
}

if (isset($_POST["eliminar_mesa"])) {
    requireValidCsrfToken("admin_mesas");

    $idMesa = validatePositiveInt($_POST["id_mesa"] ?? null);

    if (!$idMesa) {
        flashAndRedirect("admin_mesas", "Mesa no valida para eliminar.");
    }

    if (!$mesaModel->obtenerPorId($idMesa)) {
        flashAndRedirect("admin_mesas", "La mesa indicada no existe.");
    }

    if ($mesaModel->tieneReservas($idMesa)) {
        flashAndRedirect("admin_mesas", "No se puede eliminar la mesa porque tiene reservas asociadas.");
    }

    if (!$mesaModel->eliminar($idMesa)) {
        flashAndRedirect("admin_mesas", "No se pudo eliminar la mesa.");
    }

    flashAndRedirect("admin_mesas", "Mesa eliminada correctamente.", "success");
}

flashAndRedirect("admin_mesas", "Accion no reconocida.");
