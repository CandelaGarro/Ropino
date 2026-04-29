<?php

if (!defined("ROOT_PATH")) {
    define("ROOT_PATH", dirname(__DIR__, 2));
}

if (!defined("BASE_URL")) {
    $baseUrl = "";

    if (!empty($_SERVER["DOCUMENT_ROOT"])) {
        $documentRoot = str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"]) ?: $_SERVER["DOCUMENT_ROOT"]);
        $projectRoot = str_replace("\\", "/", ROOT_PATH);

        if (str_starts_with($projectRoot, $documentRoot)) {
            $detectedBase = substr($projectRoot, strlen($documentRoot));
            $baseUrl = $detectedBase !== false ? rtrim($detectedBase, "/") : "";
        }
    }

    define("BASE_URL", $baseUrl);
}

/**
 * Cargar configuracion local privada (NO se sube a Git)
 * Prioridad:
 * 1. C:\xampp\private\RESTAURANTE-ROPINO\app.local.php
 * 2. config/app.local.php (compatibilidad con instalaciones antiguas)
 */
$localConfigCandidates = [
    dirname(ROOT_PATH, 2) . DIRECTORY_SEPARATOR . "private" . DIRECTORY_SEPARATOR . "RESTAURANTE-ROPINO" . DIRECTORY_SEPARATOR . "app.local.php",
    __DIR__ . DIRECTORY_SEPARATOR . "app.local.php",
];

foreach ($localConfigCandidates as $localConfigPath) {
    if (file_exists($localConfigPath)) {
        require_once $localConfigPath;
        break;
    }
}

/**
 * Configuracion por defecto (para Git)
 */
if (!defined("MAIL_HOST")) {
    define("MAIL_HOST", "smtp.gmail.com");
}

if (!defined("MAIL_PORT")) {
    define("MAIL_PORT", 587);
}

if (!defined("MAIL_USERNAME")) {
    define("MAIL_USERNAME", "ropinorestaurante@gmail.com");
}

if (!defined("MAIL_PASSWORD")) {
    define("MAIL_PASSWORD", "TU_PASSWORD_AQUI");
}

if (!defined("MAIL_ENCRYPTION")) {
    define("MAIL_ENCRYPTION", "tls");
}

if (!defined("MAIL_FROM_ADDRESS")) {
    define("MAIL_FROM_ADDRESS", "ropinorestaurante@gmail.com");
}

if (!defined("MAIL_FROM_NAME")) {
    define("MAIL_FROM_NAME", "Ropino");
}

if (!defined("MAIL_ADMIN_ADDRESS")) {
    define("MAIL_ADMIN_ADDRESS", "ropinorestaurante@gmail.com");
}

if (!defined("MAIL_TIMEOUT_SECONDS")) {
    define("MAIL_TIMEOUT_SECONDS", 5);
}

if (!defined("MAIL_QUEUE_MAX_ATTEMPTS")) {
    define("MAIL_QUEUE_MAX_ATTEMPTS", 3);
}

if (!defined("MAIL_QUEUE_BATCH_SIZE")) {
    define("MAIL_QUEUE_BATCH_SIZE", 25);
}
