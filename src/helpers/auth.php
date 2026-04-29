<?php

require_once __DIR__ . "/../config/app.php";

function isHttpsRequest()
{
    if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") {
        return true;
    }

    if (isset($_SERVER["SERVER_PORT"]) && (int) $_SERVER["SERVER_PORT"] === 443) {
        return true;
    }

    return false;
}

function configureSession()
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    ini_set("session.use_strict_mode", "1");
    ini_set("session.use_only_cookies", "1");
    ini_set("session.cookie_httponly", "1");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => isHttpsRequest(),
        "httponly" => true,
        "samesite" => "Lax",
    ]);
}

function startSessionIfNeeded()
{
    if (session_status() === PHP_SESSION_NONE) {
        configureSession();
        session_start();
    }
}

function currentUser()
{
    startSessionIfNeeded();

    if (!isset($_SESSION["id_usuario"])) {
        return null;
    }

    return [
        "id_usuario" => (int) $_SESSION["id_usuario"],
        "nombre" => $_SESSION["nombre"] ?? "",
        "email" => $_SESSION["email"] ?? "",
        "rol" => $_SESSION["rol"] ?? "",
    ];
}

function loginUser($usuario)
{
    startSessionIfNeeded();
    session_regenerate_id(true);

    $_SESSION["id_usuario"] = (int) $usuario["id_usuario"];
    $_SESSION["nombre"] = $usuario["nombre"] ?? "";
    $_SESSION["email"] = $usuario["email"] ?? "";
    $_SESSION["rol"] = $usuario["rol"] ?? "";
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function updateSessionUserData($usuario)
{
    startSessionIfNeeded();

    $_SESSION["id_usuario"] = (int) ($usuario["id_usuario"] ?? ($_SESSION["id_usuario"] ?? 0));
    $_SESSION["nombre"] = $usuario["nombre"] ?? ($_SESSION["nombre"] ?? "");
    $_SESSION["email"] = $usuario["email"] ?? ($_SESSION["email"] ?? "");
    $_SESSION["rol"] = $usuario["rol"] ?? ($_SESSION["rol"] ?? "");
}

function logoutUser()
{
    startSessionIfNeeded();
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

function runAfterResponse(callable $callback)
{
    if (!isset($GLOBALS["after_response_callbacks"]) || !is_array($GLOBALS["after_response_callbacks"])) {
        $GLOBALS["after_response_callbacks"] = [];
    }

    $GLOBALS["after_response_callbacks"][] = $callback;
}

function executeAfterResponseCallbacks()
{
    $callbacks = $GLOBALS["after_response_callbacks"] ?? [];
    $GLOBALS["after_response_callbacks"] = [];

    if (empty($callbacks)) {
        return;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    ignore_user_abort(true);

    if (function_exists("apache_setenv")) {
        @apache_setenv("no-gzip", "1");
    }

    @ini_set("zlib.output_compression", "0");

    if (!headers_sent()) {
        header("Connection: close");
        header("Content-Length: 0");
    }

    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    flush();

    foreach ($callbacks as $callback) {
        try {
            $callback();
        } catch (Throwable $exception) {
            error_log("Error en tarea diferida: " . $exception->getMessage());
        }
    }
}

function redirectToPage($page, $params = [])
{
    $query = array_merge(["page" => $page], $params);
    header("Location: " . BASE_URL . "/index.php?" . http_build_query($query));
    executeAfterResponseCallbacks();
    exit;
}

function redirectByRole($usuario)
{
    $destino = ($usuario["rol"] ?? "") === "admin" ? "panel" : "home";
    redirectToPage($destino);
}

function requireAuth($rol = null)
{
    $usuario = currentUser();

    if (!$usuario) {
        addFlashMessage("error", "Debes iniciar sesion para continuar.");
        redirectToPage("login");
    }

    if ($rol !== null && ($usuario["rol"] ?? "") !== $rol) {
        addFlashMessage("error", "No tienes permisos para acceder a esa seccion.");
        redirectByRole($usuario);
    }

    return $usuario;
}

function requirePostRequest($page, $params = [])
{
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
        addFlashMessage("error", "Metodo no permitido.");
        redirectToPage($page, $params);
    }
}

function addFlashMessage($type, $message)
{
    startSessionIfNeeded();
    $_SESSION["flash_messages"][] = [
        "type" => $type,
        "message" => $message,
    ];
}

function consumeFlashMessages()
{
    startSessionIfNeeded();
    $messages = $_SESSION["flash_messages"] ?? [];
    unset($_SESSION["flash_messages"]);

    return is_array($messages) ? $messages : [];
}

function renderFlashMessages()
{
    $messages = consumeFlashMessages();

    if (empty($messages)) {
        return;
    }

    echo '<div class="flash-stack">';

    foreach ($messages as $message) {
        $type = $message["type"] ?? "info";
        $allowedTypes = ["success", "error", "warning", "info"];

        if (!in_array($type, $allowedTypes, true)) {
            $type = "info";
        }

        echo '<div class="flash-message flash-' . htmlspecialchars($type, ENT_QUOTES, "UTF-8") . '">';
        echo htmlspecialchars($message["message"] ?? "", ENT_QUOTES, "UTF-8");
        echo "</div>";
    }

    echo "</div>";
}

function storeOldInput($input)
{
    startSessionIfNeeded();

    if (!is_array($input)) {
        return;
    }

    foreach (array_keys($input) as $key) {
        if (!is_string($key)) {
            continue;
        }

        if (stripos($key, "password") !== false) {
            unset($input[$key]);
        }
    }

    unset($input["csrf_token"]);
    $_SESSION["old_input"] = $input;
}

function oldInput($key, $default = "")
{
    startSessionIfNeeded();
    $value = $_SESSION["old_input"][$key] ?? $default;

    if (!is_scalar($value)) {
        return $default;
    }

    return (string) $value;
}

function clearOldInput()
{
    startSessionIfNeeded();
    unset($_SESSION["old_input"]);
}

function flashAndRedirect($page, $message, $type = "error", $params = [], $oldInput = [])
{
    addFlashMessage($type, $message);

    if (!empty($oldInput)) {
        storeOldInput($oldInput);
    } else {
        clearOldInput();
    }

    redirectToPage($page, $params);
}

function getCsrfToken()
{
    startSessionIfNeeded();

    if (empty($_SESSION["csrf_token"]) || !is_string($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function csrfInput()
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(getCsrfToken(), ENT_QUOTES, "UTF-8") .
        '">';
}

function hasValidCsrfToken()
{
    startSessionIfNeeded();

    $requestToken = $_POST["csrf_token"] ?? "";
    $sessionToken = $_SESSION["csrf_token"] ?? "";

    if (!is_string($requestToken) || !is_string($sessionToken) || $sessionToken === "") {
        return false;
    }

    return hash_equals($sessionToken, $requestToken);
}

function requireValidCsrfToken($page, $params = [], $oldInput = [])
{
    if (!hasValidCsrfToken()) {
        flashAndRedirect(
            $page,
            "La sesion del formulario ha caducado. Vuelve a intentarlo.",
            "error",
            $params,
            $oldInput
        );
    }
}
