<?php

require_once __DIR__ . "/../helpers/auth.php";
require_once __DIR__ . "/../helpers/validation.php";
require_once __DIR__ . "/../models/usuario.php";
require_once __DIR__ . "/../models/mesa.php";
require_once __DIR__ . "/../models/habitacion.php";
require_once __DIR__ . "/../models/reserva.php";

class PageController
{
    public function mostrarLogin()
    {
        $usuarioActual = currentUser();

        if ($usuarioActual) {
            redirectByRole($usuarioActual);
        }

        $this->render("auth/login");
    }

    public function mostrarRegistro()
    {
        $usuarioActual = currentUser();

        if ($usuarioActual) {
            redirectByRole($usuarioActual);
        }

        $this->render("auth/registro");
    }

    public function mostrarHome()
    {
        $usuarioActual = currentUser();
        $this->render("public/home", compact("usuarioActual"));
    }

    public function mostrarReservas()
    {
        $usuarioActual = requireAuth("cliente");
        $habitacionModel = new Habitacion();
        $reservaModel = new Reserva();
        $habitaciones = $habitacionModel->obtenerDisponiblesParaReserva();
        $reservas = $reservaModel->obtenerPorUsuario($usuarioActual["id_usuario"]);
        $duracionMesaMinutos = Reserva::DURACION_MESA_MINUTOS;

        $this->render("cliente/reservas", compact("usuarioActual", "habitaciones", "reservas", "duracionMesaMinutos"));
    }

    public function mostrarMisDatos()
    {
        $usuarioActual = requireAuth();
        $rolLabel = ($usuarioActual["rol"] ?? "") === "admin" ? "Administrador" : "Cliente";

        $this->render("cuenta/mis_datos", compact("usuarioActual", "rolLabel"));
    }

    public function mostrarConfiguracion()
    {
        $usuarioActual = requireAuth();
        $rolLabel = ($usuarioActual["rol"] ?? "") === "admin" ? "Administrador" : "Cliente";

        $this->render("cuenta/configuracion", compact("usuarioActual", "rolLabel"));
    }

    public function mostrarPanelAdmin()
    {
        $usuarioActual = requireAuth("admin");
        $reservaModel = new Reserva();
        $hoy = new DateTimeImmutable("now", new DateTimeZone("Europe/Madrid"));
        $fechaSeleccionada = $hoy->format("Y-m-d");
        $fechaResumenTexto = $hoy->format("d/m/Y");
        $reservas = $reservaModel->obtenerTodasFiltradas();
        $panelOperativo = $this->construirPanelOperativo(
            $reservas,
            $fechaSeleccionada,
            ""
        );
        $reservasPendientes = $this->obtenerPendientesPorConfirmar($reservas);

        $this->render(
            "admin/panel",
            compact(
                "usuarioActual",
                "fechaSeleccionada",
                "fechaResumenTexto",
                "panelOperativo",
                "reservasPendientes"
            )
        );
    }

    public function mostrarReportes()
    {
        $usuarioActual = requireAuth("admin");
        $mesaModel = new Mesa();
        $habitacionModel = new Habitacion();
        $reservaModel = new Reserva();
        $fechaSeleccionada = validateDateValue($_GET["fecha"] ?? "") ?? date("Y-m-d");
        $filtroTipo = validateEnumValue($_GET["tipo"] ?? "", ["habitacion", "mesa"]) ?? "";
        $filtroEstadoOperativo = validateEnumValue(
            $_GET["estado_operativo"] ?? "",
            ["pendiente", "confirmada", "cancelada", "en_curso", "finalizada"]
        ) ?? "";
        $busqueda = trim((string) ($_GET["busqueda"] ?? ""));
        $busqueda = substr($busqueda, 0, 80);
        $reservas = $reservaModel->obtenerTodasFiltradas($filtroTipo, "", $busqueda);
        $panelOperativo = $this->construirPanelOperativo(
            $reservas,
            $fechaSeleccionada,
            $filtroEstadoOperativo
        );
        $mesasPorEstado = [
            "disponibles" => $mesaModel->contarPorEstado("disponible"),
            "ocupadas" => $mesaModel->contarPorEstado("ocupada"),
            "total" => $mesaModel->contarTotal(),
        ];
        $habitacionesPorEstado = [
            "disponibles" => $habitacionModel->contarPorEstado("disponible"),
            "ocupadas" => $habitacionModel->contarPorEstado("ocupada"),
            "mantenimiento" => $habitacionModel->contarPorEstado("mantenimiento"),
            "total" => $habitacionModel->contarTotal(),
        ];

        $this->render(
            "admin/reportes",
            compact(
                "usuarioActual",
                "fechaSeleccionada",
                "filtroTipo",
                "filtroEstadoOperativo",
                "busqueda",
                "panelOperativo",
                "mesasPorEstado",
                "habitacionesPorEstado"
            )
        );
    }

    public function mostrarAdminReservas()
    {
        $usuarioActual = requireAuth("admin");
        $reservaModel = new Reserva();
        $filtroTipo = trim($_GET["tipo"] ?? "");
        $filtroEstado = trim($_GET["estado"] ?? "");
        $busqueda = trim($_GET["busqueda"] ?? "");

        $totalReservas = $reservaModel->contarTodas();
        $totalConfirmadas = $reservaModel->contarPorEstado("confirmada");
        $totalCanceladas = $reservaModel->contarPorEstado("cancelada");
        $reservas = $reservaModel->obtenerTodasFiltradas($filtroTipo, $filtroEstado, $busqueda);

        $this->render(
            "admin/admin_reservas",
            compact(
                "usuarioActual",
                "filtroTipo",
                "filtroEstado",
                "busqueda",
                "totalReservas",
                "totalConfirmadas",
                "totalCanceladas",
                "reservas"
            )
        );
    }

    public function mostrarAdminMesas()
    {
        $usuarioActual = requireAuth("admin");
        $mesaModel = new Mesa();
        $mesas = $mesaModel->obtenerTodas();
        $mesaEditar = null;

        if (isset($_GET["editar"])) {
            $mesaEditar = $mesaModel->obtenerPorId((int) $_GET["editar"]);
        }

        $this->render("admin/admin_mesas", compact("usuarioActual", "mesas", "mesaEditar"));
    }

    public function mostrarAdminHabitaciones()
    {
        $usuarioActual = requireAuth("admin");
        $habitacionModel = new Habitacion();
        $habitaciones = $habitacionModel->obtenerTodas();
        $habitacionEditar = null;

        if (isset($_GET["editar"])) {
            $habitacionEditar = $habitacionModel->obtenerPorId((int) $_GET["editar"]);
        }

        $this->render(
            "admin/admin_habitaciones",
            compact("usuarioActual", "habitaciones", "habitacionEditar")
        );
    }

    private function render($view, $data = [])
    {
        extract($data, EXTR_SKIP);
        require ROOT_PATH . "/views/" . $view . ".php";
        clearOldInput();
    }

    private function construirPanelOperativo($reservas, $fechaSeleccionada, $filtroEstadoOperativo)
    {
        $panel = [
            "agenda_dia" => [],
            "proximas_reservas" => [],
            "total_dia" => 0,
            "mesas_dia" => 0,
            "habitaciones_dia" => 0,
            "pendientes_dia" => 0,
            "en_curso_dia" => 0,
            "canceladas_dia" => 0,
        ];

        foreach ($reservas as $reserva) {
            if (
                $this->reservaCorrespondeAFechaOperativa($reserva, $fechaSeleccionada) &&
                $this->reservaCoincideEstadoOperativo($reserva, $filtroEstadoOperativo)
            ) {
                $panel["agenda_dia"][] = $reserva;
                $panel["total_dia"]++;

                if (($reserva["tipo_reserva"] ?? "") === "mesa") {
                    $panel["mesas_dia"]++;
                } elseif (($reserva["tipo_reserva"] ?? "") === "habitacion") {
                    $panel["habitaciones_dia"]++;
                }

                if (($reserva["estado_codigo"] ?? "") === "pendiente") {
                    $panel["pendientes_dia"]++;
                }

                if (($reserva["estado_codigo"] ?? "") === "en_curso") {
                    $panel["en_curso_dia"]++;
                }

                if (($reserva["estado_codigo"] ?? "") === "cancelada") {
                    $panel["canceladas_dia"]++;
                }
            }

            if ($this->reservaEsProximaOperativa($reserva, $fechaSeleccionada)) {
                $panel["proximas_reservas"][] = $reserva;
            }
        }

        usort($panel["agenda_dia"], function ($reservaA, $reservaB) {
            return strcmp(
                $this->obtenerMomentoOrdenReserva($reservaA),
                $this->obtenerMomentoOrdenReserva($reservaB)
            );
        });

        usort($panel["proximas_reservas"], function ($reservaA, $reservaB) {
            return strcmp(
                $this->obtenerMomentoOrdenReserva($reservaA),
                $this->obtenerMomentoOrdenReserva($reservaB)
            );
        });

        $panel["proximas_reservas"] = array_slice($panel["proximas_reservas"], 0, 8);

        return $panel;
    }

    private function reservaCorrespondeAFechaOperativa($reserva, $fechaSeleccionada)
    {
        if (($reserva["tipo_reserva"] ?? "") === "mesa") {
            return ($reserva["fecha_inicio"] ?? "") === $fechaSeleccionada;
        }

        $fechaInicio = $reserva["fecha_inicio"] ?? "";
        $fechaFin = $reserva["fecha_fin"] ?? $fechaInicio;

        return $fechaInicio <= $fechaSeleccionada && $fechaFin > $fechaSeleccionada;
    }

    private function reservaCoincideEstadoOperativo($reserva, $filtroEstadoOperativo)
    {
        if ($filtroEstadoOperativo === "") {
            return true;
        }

        return ($reserva["estado_codigo"] ?? "") === $filtroEstadoOperativo;
    }

    private function reservaEsProximaOperativa($reserva, $fechaSeleccionada)
    {
        $estadoCodigo = $reserva["estado_codigo"] ?? "";

        if ($estadoCodigo !== "pendiente" && $estadoCodigo !== "confirmada" && $estadoCodigo !== "en_curso") {
            return false;
        }

        if ($this->reservaCorrespondeAFechaOperativa($reserva, $fechaSeleccionada)) {
            return true;
        }

        return $this->obtenerMomentoOrdenReserva($reserva) >= $fechaSeleccionada . " 00:00";
    }

    private function obtenerMomentoOrdenReserva($reserva)
    {
        $fecha = $reserva["fecha_inicio"] ?? "9999-12-31";

        if (($reserva["tipo_reserva"] ?? "") === "mesa") {
            return $fecha . " " . ($reserva["hora_reserva"] ?? "23:59");
        }

        return $fecha . " 00:00";
    }

    private function obtenerPendientesPorConfirmar($reservas)
    {
        $pendientes = array_values(array_filter($reservas, function ($reserva) {
            return ($reserva["estado_codigo"] ?? "") === "pendiente";
        }));

        usort($pendientes, function ($reservaA, $reservaB) {
            return strcmp(
                $this->obtenerMomentoOrdenReserva($reservaA),
                $this->obtenerMomentoOrdenReserva($reservaB)
            );
        });

        return array_slice($pendientes, 0, 8);
    }

    public function mostrarCasaRural()
    {
        $usuarioActual = currentUser();
        $this->render("info/casa_rural", compact("usuarioActual"));
    }

    public function mostrarApartamentos()
    {
        $usuarioActual = currentUser();
        $this->render("info/apartamentos", compact("usuarioActual"));
    }

    public function mostrarRestaurante()
    {
        $usuarioActual = currentUser();
        $this->render("info/restaurante", compact("usuarioActual"));
    }
}
