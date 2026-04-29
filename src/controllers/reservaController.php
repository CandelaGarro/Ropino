<?php

require_once __DIR__ . "/../helpers/auth.php";
require_once __DIR__ . "/../helpers/validation.php";
require_once __DIR__ . "/../models/habitacion.php";
require_once __DIR__ . "/../models/mesa.php";
require_once __DIR__ . "/../models/reserva.php";
require_once __DIR__ . "/../helpers/mail.php";

$usuarioActual = requireAuth("cliente");
requirePostRequest("reservas");

$reservaModel = new Reserva();
$mesaModel = new Mesa();
$habitacionModel = new Habitacion();

if (isset($_POST["crear_reserva"])) {
    requireValidCsrfToken("reservas", [], $_POST);

    $idUsuario = (int) $usuarioActual["id_usuario"];
    $tipoReserva = validateEnumValue($_POST["tipo_reserva"] ?? "", ["habitacion", "mesa"]);

    if (!$tipoReserva) {
        flashAndRedirect("reservas", "Selecciona un tipo de reserva valido.", "error", [], $_POST);
    }

    if ($tipoReserva === "habitacion") {
        $alojamientosSeleccionados = $_POST["alojamientos"] ?? [];
        $fechaInicio = validateDateValue($_POST["fecha_inicio"] ?? "");
        $fechaFin = validateDateValue($_POST["fecha_fin"] ?? "");

        if (!is_array($alojamientosSeleccionados) || empty($alojamientosSeleccionados) || !$fechaInicio || !$fechaFin) {
            flashAndRedirect("reservas", "Debes seleccionar al menos un alojamiento y completar correctamente las fechas.", "error", [], $_POST);
        }

        if (!isDateOnOrAfterToday($fechaInicio)) {
            flashAndRedirect("reservas", "La fecha de entrada no puede estar en el pasado.", "error", [], $_POST);
        }

        if (!isDateRangeValid($fechaInicio, $fechaFin)) {
            flashAndRedirect("reservas", "La fecha de salida debe ser posterior a la de entrada.", "error", [], $_POST);
        }

        $idsHabitaciones = [];
        foreach ($alojamientosSeleccionados as $alojamiento) {
            $idHabitacion = validatePositiveInt($alojamiento);
            if ($idHabitacion) {
                $idsHabitaciones[] = $idHabitacion;
            }
        }

        $idsHabitaciones = array_values(array_unique($idsHabitaciones));

        if (empty($idsHabitaciones)) {
            flashAndRedirect("reservas", "Debes seleccionar alojamientos validos.", "error", [], $_POST);
        }

        $habitacionesSeleccionadas = [];
        $hayCasaCompleta = false;

        foreach ($idsHabitaciones as $idHabitacion) {
            $habitacion = $habitacionModel->obtenerPorId($idHabitacion);

            if (!$habitacion) {
                flashAndRedirect("reservas", "Uno de los alojamientos seleccionados no existe.", "error", [], $_POST);
            }

            if (($habitacion["estado"] ?? "") !== "disponible") {
                flashAndRedirect(
                    "reservas",
                    "El alojamiento " . ($habitacion["nombre"] ?? $idHabitacion) . " no esta disponible.",
                    "error",
                    [],
                    $_POST
                );
            }

            if ($reservaModel->haySolapeHabitacion($idHabitacion, $fechaInicio, $fechaFin)) {
                flashAndRedirect(
                    "reservas",
                    "El alojamiento " . ($habitacion["nombre"] ?? $idHabitacion) . " ya esta reservado en esas fechas.",
                    "error",
                    [],
                    $_POST
                );
            }

            if (($habitacion["tipo_alojamiento"] ?? "") === "completo") {
                $hayCasaCompleta = true;
            }

            $habitacionesSeleccionadas[] = $habitacion;
        }

        if ($hayCasaCompleta && count($habitacionesSeleccionadas) > 1) {
            flashAndRedirect(
                "reservas",
                "Si seleccionas Casa completa no puedes elegir otros alojamientos a la vez.",
                "error",
                [],
                $_POST
            );
        }

        foreach ($habitacionesSeleccionadas as $habitacion) {
            if (!$reservaModel->crearReservaHabitacion(
                $idUsuario,
                (int) $habitacion["id_habitacion"],
                $fechaInicio,
                $fechaFin
            )) {
                flashAndRedirect(
                    "reservas",
                    "No se pudo crear la reserva del alojamiento " . ($habitacion["nombre"] ?? $habitacion["id_habitacion"]) . ".",
                    "error",
                    [],
                    $_POST
                );
            }
        }

        $nombresAlojamientos = array_map(function ($habitacion) {
            return $habitacion["nombre"] ?? ("Alojamiento " . $habitacion["id_habitacion"]);
        }, $habitacionesSeleccionadas);

        $detalle = implode(", ", $nombresAlojamientos) . " del " . $fechaInicio . " al " . $fechaFin;

        queueAdminNewReservationEmail(
            $usuarioActual["nombre"] ?? "Cliente",
            $usuarioActual["email"] ?? "",
            "habitacion",
            $detalle
        );

        queueClientReservationReceivedEmail(
            $usuarioActual["email"] ?? "",
            $usuarioActual["nombre"] ?? "Cliente",
            $detalle
        );

        flashAndRedirect("reservas", "Reserva de alojamiento enviada correctamente. Queda pendiente de confirmacion.", "success");
    }

    $comensales = validatePositiveInt($_POST["comensales"] ?? null, 1, 20);
    $fechaMesa = validateDateValue($_POST["fecha_mesa"] ?? "");
    $horaMesa = validateTimeValue($_POST["hora_mesa"] ?? "");
    $ubicacionPreferida = validateEnumValue($_POST["ubicacion_preferida"] ?? "", ["interior", "terraza"]);

    if (!$comensales || !$fechaMesa || !$horaMesa || !$ubicacionPreferida) {
        flashAndRedirect("reservas", "Debes completar correctamente los datos de la mesa.", "error", [], $_POST);
    }

    if (!isDateOnOrAfterToday($fechaMesa)) {
        flashAndRedirect("reservas", "La fecha de la reserva no puede estar en el pasado.", "error", [], $_POST);
    }

    if (!isDateTimeInFuture($fechaMesa, $horaMesa)) {
        flashAndRedirect("reservas", "La hora de la reserva debe ser actual o futura.", "error", [], $_POST);
    }

    if ($horaMesa < Reserva::HORA_MESA_INICIO || $horaMesa > Reserva::HORA_MESA_FIN) {
        flashAndRedirect(
            "reservas",
            "La hora de la reserva debe estar entre " . Reserva::HORA_MESA_INICIO . " y " . Reserva::HORA_MESA_FIN . ".",
            "error",
            [],
            $_POST
        );
    }

    $mesaAsignada = $mesaModel->buscarDisponible(
        $comensales,
        $ubicacionPreferida,
        $fechaMesa,
        $horaMesa
    );

    if (!$mesaAsignada) {
        flashAndRedirect(
            "reservas",
            "No hay mesas disponibles para ese numero de comensales, fecha, hora y ubicacion.",
            "error",
            [],
            $_POST
        );
    }

    if (!$reservaModel->crearReservaMesa(
        $idUsuario,
        (int) $mesaAsignada["id_mesa"],
        $fechaMesa,
        $horaMesa,
        $comensales,
        $ubicacionPreferida
    )) {
        flashAndRedirect("reservas", "No se pudo crear la reserva de mesa.", "error", [], $_POST);
    }

    $detalle = "Mesa para " . $comensales . " personas el " . $fechaMesa . " a las " . $horaMesa;

    queueAdminNewReservationEmail(
        $usuarioActual["nombre"] ?? "Cliente",
        $usuarioActual["email"] ?? "",
        "mesa",
        $detalle
    );

    queueClientReservationReceivedEmail(
        $usuarioActual["email"] ?? "",
        $usuarioActual["nombre"] ?? "Cliente",
        $detalle
    );

    flashAndRedirect("reservas", "Reserva de mesa enviada correctamente. Queda pendiente de confirmacion.", "success");
}

if (isset($_POST["cancelar_reserva"])) {
    requireValidCsrfToken("reservas");

    $idReserva = validatePositiveInt($_POST["id_reserva"] ?? null);

    if (!$idReserva) {
        flashAndRedirect("reservas", "Reserva no valida.");
    }

    $reserva = $reservaModel->obtenerPorId($idReserva);

    if (!$reserva || (int) ($reserva["id_usuario"] ?? 0) !== (int) $usuarioActual["id_usuario"]) {
        flashAndRedirect("reservas", "La reserva indicada no existe o no te pertenece.");
    }

    if (empty($reserva["cancelable_cliente"])) {
        flashAndRedirect("reservas", "Solo puedes cancelar reservas futuras.");
    }

    if (!$reservaModel->cancelarPorUsuario($idReserva, (int) $usuarioActual["id_usuario"])) {
        flashAndRedirect("reservas", "No se pudo cancelar la reserva indicada.");
    }

    flashAndRedirect("reservas", "Reserva cancelada correctamente.", "success");
}

flashAndRedirect("reservas", "Accion no reconocida.");
