<?php

require_once __DIR__ . "/../helpers/auth.php";
require_once __DIR__ . "/../helpers/validation.php";
require_once __DIR__ . "/../helpers/mail.php";
require_once __DIR__ . "/../models/mesa.php";
require_once __DIR__ . "/../models/reserva.php";

$usuarioActual = requireAuth("admin");
requirePostRequest("admin_reservas");

$reservaModel = new Reserva();
$mesaModel = new Mesa();
$returnPage = validateEnumValue($_POST["return_page"] ?? "", ["admin_reservas", "panel"]) ?? "admin_reservas";

if (isset($_POST["confirmar_reserva_admin"])) {
    requireValidCsrfToken($returnPage);

    $idReserva = validatePositiveInt($_POST["id_reserva"] ?? null);

    if (!$idReserva) {
        flashAndRedirect($returnPage, "Reserva no valida.");
    }

    $reserva = $reservaModel->obtenerPorId($idReserva);

    if (!$reserva) {
        flashAndRedirect($returnPage, "La reserva no existe.");
    }

    if (($reserva["estado"] ?? "") !== Reserva::ESTADO_PENDIENTE) {
        flashAndRedirect($returnPage, "Solo se pueden confirmar reservas pendientes.");
    }

    $mensajeConfirmacion = "Reserva confirmada correctamente.";

    if (($reserva["tipo_reserva"] ?? "") === "mesa") {
        $horaReserva = substr((string) ($reserva["hora_reserva"] ?? ""), 0, 5);
        $mesaActualDisponible = $mesaModel->estaDisponibleParaReserva(
            (int) ($reserva["id_mesa"] ?? 0),
            (string) ($reserva["fecha_inicio"] ?? ""),
            $horaReserva,
            [Reserva::ESTADO_CONFIRMADA],
            $idReserva
        );

        if (!$mesaActualDisponible) {
            $mesaDisponible = $mesaModel->buscarDisponible(
                (int) ($reserva["comensales"] ?? 0),
                (string) ($reserva["ubicacion_preferida"] ?? ""),
                (string) ($reserva["fecha_inicio"] ?? ""),
                $horaReserva,
                [Reserva::ESTADO_PENDIENTE, Reserva::ESTADO_CONFIRMADA],
                $idReserva
            );

            if (!$mesaDisponible) {
                flashAndRedirect(
                    $returnPage,
                    "No queda ninguna mesa disponible para esa fecha y hora. Cancela la reserva o revisa los datos."
                );
            }

            if (!$reservaModel->reasignarMesa($idReserva, (int) $mesaDisponible["id_mesa"])) {
                flashAndRedirect($returnPage, "No se pudo reasignar una mesa disponible antes de confirmar.");
            }

            $reserva = $reservaModel->obtenerPorId($idReserva) ?? $reserva;
            $mensajeConfirmacion = "Reserva confirmada correctamente. Se ha reasignado otra mesa disponible.";
        }
    }

    if (!$reservaModel->confirmarComoAdmin($idReserva)) {
        flashAndRedirect($returnPage, "No se pudo confirmar la reserva.");
    }

    $detalle = $reserva["detalle_reserva_publico"] ?? $reserva["detalle_reserva"] ?? ("Reserva #" . $idReserva);

    queueClientReservationStatusEmail(
        $reserva["email_usuario"] ?? "",
        $reserva["nombre_usuario"] ?? "Cliente",
        $detalle,
        "Confirmada"
    );

    flashAndRedirect($returnPage, $mensajeConfirmacion, "success");
}

if (isset($_POST["cancelar_reserva_admin"])) {
    requireValidCsrfToken($returnPage);

    $idReserva = validatePositiveInt($_POST["id_reserva"] ?? null);

    if (!$idReserva) {
        flashAndRedirect($returnPage, "Reserva no valida.");
    }

    $reserva = $reservaModel->obtenerPorId($idReserva);

    if (!$reserva) {
        flashAndRedirect($returnPage, "La reserva no existe.");
    }

    if (($reserva["estado"] ?? "") === Reserva::ESTADO_PENDIENTE) {
        if (!$reservaModel->cancelarPendienteComoAdmin($idReserva)) {
            flashAndRedirect($returnPage, "No se pudo cancelar la reserva pendiente.");
        }

        $detalle = $reserva["detalle_reserva_publico"] ?? $reserva["detalle_reserva"] ?? ("Reserva #" . $idReserva);

        queueClientReservationStatusEmail(
            $reserva["email_usuario"] ?? "",
            $reserva["nombre_usuario"] ?? "Cliente",
            $detalle,
            "Cancelada"
        );

        flashAndRedirect($returnPage, "Reserva pendiente cancelada correctamente.", "success");
    }

    if (!$reservaModel->cancelarComoAdmin($idReserva)) {
        flashAndRedirect($returnPage, "No se pudo cancelar la reserva.");
    }

    $detalle = $reserva["detalle_reserva_publico"] ?? $reserva["detalle_reserva"] ?? ("Reserva #" . $idReserva);

    queueClientReservationStatusEmail(
        $reserva["email_usuario"] ?? "",
        $reserva["nombre_usuario"] ?? "Cliente",
        $detalle,
        "Cancelada"
    );

    flashAndRedirect($returnPage, "Reserva cancelada correctamente.", "success");
}

flashAndRedirect("admin_reservas", "Accion no reconocida.");
