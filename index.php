<?php

require_once __DIR__ . "/src/controllers/pageController.php";

$page = $_GET["page"] ?? "home";
$controller = new PageController();

switch ($page) {

    case "login":
        $controller->mostrarLogin();
        break;

    case "registro":
        $controller->mostrarRegistro();
        break;

    case "home":
        $controller->mostrarHome();
        break;

    case "reservas":
        $controller->mostrarReservas();
        break;

    case "mis_datos":
        $controller->mostrarMisDatos();
        break;

    case "configuracion":
        $controller->mostrarConfiguracion();
        break;

    case "panel":
        $controller->mostrarPanelAdmin();
        break;

    case "reportes":
        $controller->mostrarReportes();
        break;

    case "admin_reservas":
        $controller->mostrarAdminReservas();
        break;

    case "admin_mesas":
        $controller->mostrarAdminMesas();
        break;

    case "admin_habitaciones":
        $controller->mostrarAdminHabitaciones();
        break;

    case "casa_rural":
        $controller->mostrarCasaRural();
        break;

    case "apartamentos":
        $controller->mostrarApartamentos();
        break;

    case "restaurante":
        $controller->mostrarRestaurante();
        break;

    default:
        http_response_code(404);
        echo "Pagina no encontrada";
        break;
}
