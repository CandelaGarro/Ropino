<?php

require_once __DIR__ . "/src/helpers/auth.php";

requirePostRequest("home");

if (!hasValidCsrfToken()) {
    addFlashMessage("error", "No se pudo cerrar la sesion. Intentalo de nuevo.");
    redirectToPage("home");
}

logoutUser();
startSessionIfNeeded();
addFlashMessage("success", "Sesion cerrada correctamente.");

redirectToPage("home");
