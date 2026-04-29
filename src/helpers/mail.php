<?php

require_once __DIR__ . "/../config/app.php";

function requireMailDependencies(): void
{
    $autoloadPath = ROOT_PATH . "/vendor/autoload.php";

    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }

    if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
        return;
    }

    $candidateBases = [
        ROOT_PATH . "/vendor/phpmailer/phpmailer/src",
        __DIR__ . "/phpmailer",
    ];

    foreach ($candidateBases as $phpMailerBase) {
        $requiredFiles = [
            $phpMailerBase . "/Exception.php",
            $phpMailerBase . "/PHPMailer.php",
            $phpMailerBase . "/SMTP.php",
        ];

        foreach ($requiredFiles as $requiredFile) {
            if (file_exists($requiredFile)) {
                require_once $requiredFile;
            }
        }

        if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            return;
        }
    }
}

requireMailDependencies();

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function createMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->Port = MAIL_PORT;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION;
    $mail->Timeout = MAIL_TIMEOUT_SECONDS;
    $mail->CharSet = "UTF-8";
    $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
    $mail->isHTML(true);

    return $mail;
}

function sendEmail(string $to, string $subject, string $htmlBody, string $plainBody = ""): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    try {
        $mail = createMailer();
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $plainBody !== "" ? $plainBody : trim(preg_replace("/\s+/", " ", strip_tags($htmlBody)));

        return $mail->send();
    } catch (Throwable $e) {
        error_log("Error enviando email: " . $e->getMessage());
        return false;
    }
}

function getMailQueueDirectory(): string
{
    return ROOT_PATH . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "mail-queue";
}

function getMailQueueFilePath(): string
{
    return getMailQueueDirectory() . DIRECTORY_SEPARATOR . "queue.jsonl";
}

function getMailQueueWorkerLockPath(): string
{
    return getMailQueueDirectory() . DIRECTORY_SEPARATOR . "worker.lock";
}

function getMailDebugLogPath(): string
{
    return getMailQueueDirectory() . DIRECTORY_SEPARATOR . "mail-debug.log";
}

function ensureMailQueueDirectory(): bool
{
    $directory = getMailQueueDirectory();

    if (is_dir($directory)) {
        return true;
    }

    return @mkdir($directory, 0775, true) || is_dir($directory);
}

function appendMailQueueEntries(array $entries): bool
{
    if (empty($entries) || !ensureMailQueueDirectory()) {
        return false;
    }

    $queuePath = getMailQueueFilePath();
    $handle = @fopen($queuePath, "c+");

    if (!$handle) {
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }

        fseek($handle, 0, SEEK_END);

        foreach ($entries as $entry) {
            fwrite($handle, json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        }

        fflush($handle);
        flock($handle, LOCK_UN);

        return true;
    } finally {
        fclose($handle);
    }
}

function pullMailQueueEntries(): array
{
    if (!ensureMailQueueDirectory()) {
        return [];
    }

    $queuePath = getMailQueueFilePath();
    $handle = @fopen($queuePath, "c+");

    if (!$handle) {
        return [];
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return [];
        }

        rewind($handle);
        $entries = [];

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            if ($line === "") {
                continue;
            }

            $entry = json_decode($line, true);

            if (is_array($entry)) {
                $entries[] = $entry;
            }
        }

        ftruncate($handle, 0);
        rewind($handle);
        fflush($handle);
        flock($handle, LOCK_UN);

        return $entries;
    } finally {
        fclose($handle);
    }
}

function queueEmail(string $to, string $subject, string $htmlBody, string $plainBody = ""): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $queued = appendMailQueueEntries([[
        "to" => $to,
        "subject" => $subject,
        "html_body" => $htmlBody,
        "plain_body" => $plainBody !== "" ? $plainBody : trim(preg_replace("/\s+/", " ", strip_tags($htmlBody))),
        "attempts" => 0,
        "queued_at" => date(DATE_ATOM),
    ]]);

    if ($queued) {
        if (function_exists("runAfterResponse")) {
            runAfterResponse(function () {
                processQueuedEmails();
            });
        } else {
            dispatchMailQueueInBackground();
        }
    }

    return $queued;
}

function dispatchMailQueueInBackground(): bool
{
    $processorPath = ROOT_PATH . DIRECTORY_SEPARATOR . "scripts" . DIRECTORY_SEPARATOR . "process_mail_queue.php";

    if (!file_exists($processorPath)) {
        return false;
    }

    $phpBinary = defined("PHP_BINARY") && PHP_BINARY !== "" ? PHP_BINARY : "php";

    if (DIRECTORY_SEPARATOR === "\\") {
        $command = 'cmd /c start "" /B ' . escapeshellarg($phpBinary) . " " . escapeshellarg($processorPath);
        @pclose(@popen($command, "r"));
        return true;
    }

    $command = escapeshellarg($phpBinary) . " " . escapeshellarg($processorPath) . " > /dev/null 2>&1 &";
    @exec($command);

    return true;
}

function processQueuedEmails(int $maxBatch = MAIL_QUEUE_BATCH_SIZE): int
{
    if (!ensureMailQueueDirectory()) {
        return 0;
    }

    $lockHandle = @fopen(getMailQueueWorkerLockPath(), "c+");

    if (!$lockHandle) {
        return 0;
    }

    try {
        if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
            return 0;
        }

        $processed = 0;

        while ($processed < $maxBatch) {
            $entries = pullMailQueueEntries();

            if (empty($entries)) {
                break;
            }

            $retryEntries = [];

            foreach ($entries as $entry) {
                if ($processed >= $maxBatch) {
                    $retryEntries[] = $entry;
                    continue;
                }

                $targetEmail = (string) ($entry["to"] ?? "");

                $sent = sendEmail(
                    $targetEmail,
                    (string) ($entry["subject"] ?? ""),
                    (string) ($entry["html_body"] ?? ""),
                    (string) ($entry["plain_body"] ?? "")
                );

                if ($sent) {
                    $processed++;
                    continue;
                }

                $attempts = (int) ($entry["attempts"] ?? 0) + 1;

                if ($attempts < MAIL_QUEUE_MAX_ATTEMPTS) {
                    $entry["attempts"] = $attempts;
                    $retryEntries[] = $entry;
                    continue;
                }

                error_log("Email descartado tras varios intentos fallidos para: " . ($entry["to"] ?? "sin-destino"));
            }

            if (!empty($retryEntries)) {
                appendMailQueueEntries($retryEntries);
            }
        }

        flock($lockHandle, LOCK_UN);

        return $processed;
    } finally {
        fclose($lockHandle);
    }
}

function buildAdminNewReservationEmail(string $clienteNombre, string $clienteEmail, string $tipoReserva, string $detalle): array
{
    $subject = "🔔 Nueva reserva pendiente - Ropino";

    $html = "
        <h2>📩 Nueva solicitud de reserva</h2>

        <p>Se ha registrado una nueva reserva en el sistema.</p>

        <hr>

        <p><strong>👤 Cliente:</strong> {$clienteNombre}</p>
        <p><strong>📧 Email:</strong> {$clienteEmail}</p>
        <p><strong>📌 Tipo de reserva:</strong> {$tipoReserva}</p>
        <p><strong>📋 Detalle:</strong> {$detalle}</p>

        <hr>

        <p>Accede al panel de administración para revisarla y gestionarla.</p>

        <p style='margin-top:20px;'>— Sistema Ropino</p>
    ";

    $plain = "Nueva solicitud de reserva.\nCliente: {$clienteNombre}\nEmail: {$clienteEmail}\nTipo de reserva: {$tipoReserva}\nDetalle: {$detalle}";

    return [
        "to" => MAIL_ADMIN_ADDRESS,
        "subject" => $subject,
        "html" => $html,
        "plain" => $plain,
    ];
}

function sendAdminNewReservationEmail(string $clienteNombre, string $clienteEmail, string $tipoReserva, string $detalle): bool
{
    $email = buildAdminNewReservationEmail($clienteNombre, $clienteEmail, $tipoReserva, $detalle);
    return sendEmail($email["to"], $email["subject"], $email["html"], $email["plain"]);
}

function queueAdminNewReservationEmail(string $clienteNombre, string $clienteEmail, string $tipoReserva, string $detalle): bool
{
    $email = buildAdminNewReservationEmail($clienteNombre, $clienteEmail, $tipoReserva, $detalle);
    return queueEmail($email["to"], $email["subject"], $email["html"], $email["plain"]);
}

function buildClientReservationReceivedEmail(string $clienteEmail, string $clienteNombre, string $detalle): array
{
    $subject = "⏳ Hemos recibido tu reserva en Ropino";

    $html = "
        <h2>Reserva recibida correctamente</h2>

        <p>Hola <strong>{$clienteNombre}</strong>,</p>

        <p>Hemos recibido tu solicitud de reserva con los siguientes datos:</p>

        <p style='background:#f4f4f4;padding:10px;border-radius:8px;'>
            {$detalle}
        </p>

        <p>Actualmente tu reserva está <strong style='color:#c28a2c;'>pendiente de confirmación</strong>.</p>

        <p>Te avisaremos en cuanto sea revisada por nuestro equipo.</p>

        <p style='margin-top:20px;'>Gracias por confiar en nosotros.<br><strong>Equipo Ropino</strong></p>
    ";

    $plain = "Hola {$clienteNombre}. Hemos recibido tu solicitud de reserva con este detalle: {$detalle}. Actualmente está pendiente de confirmación.";

    return [
        "to" => $clienteEmail,
        "subject" => $subject,
        "html" => $html,
        "plain" => $plain,
    ];
}

function sendClientReservationReceivedEmail(string $clienteEmail, string $clienteNombre, string $detalle): bool
{
    $email = buildClientReservationReceivedEmail($clienteEmail, $clienteNombre, $detalle);
    return sendEmail($email["to"], $email["subject"], $email["html"], $email["plain"]);
}

function queueClientReservationReceivedEmail(string $clienteEmail, string $clienteNombre, string $detalle): bool
{
    $email = buildClientReservationReceivedEmail($clienteEmail, $clienteNombre, $detalle);
    return queueEmail($email["to"], $email["subject"], $email["html"], $email["plain"]);
}

function buildClientReservationStatusEmail(string $clienteEmail, string $clienteNombre, string $detalle, string $estado): array
{
    $estadoNormalizado = strtolower(trim($estado));
    $subject = "📌 Actualización de tu reserva - Ropino";
    $colorEstado = "#444";

    if ($estadoNormalizado === "confirmada") {
        $colorEstado = "#2e7d32";
    } elseif ($estadoNormalizado === "cancelada") {
        $colorEstado = "#c0392b";
    }

    $mensajeEstado = "Puedes consultar el estado desde tu área de usuario.";

    if ($estadoNormalizado === "confirmada") {
        $mensajeEstado = "Tu reserva ha sido confirmada. ¡Te esperamos!";
    } elseif ($estadoNormalizado === "cancelada") {
        $mensajeEstado = "Tu reserva ha sido cancelada. Si ha sido un error, puedes crear una nueva desde la web.";
    }

    $html = "
        <h2>Actualización de tu reserva</h2>

        <p>Hola <strong>{$clienteNombre}</strong>,</p>

        <p>Tu reserva ha sido actualizada:</p>

        <p style='background:#f4f4f4;padding:10px;border-radius:8px;'>
            {$detalle}
        </p>

        <p><strong>Estado actual:</strong>
            <span style='color:{$colorEstado}; font-weight:bold;'>{$estado}</span>
        </p>

        <p>{$mensajeEstado}</p>

        <p style='margin-top:20px;'>Un saludo,<br><strong>Equipo Ropino</strong></p>
    ";

    $plain = "Hola {$clienteNombre}. Tu reserva con detalle '{$detalle}' ha sido actualizada. Estado actual: {$estado}. {$mensajeEstado}";

    return [
        "to" => $clienteEmail,
        "subject" => $subject,
        "html" => $html,
        "plain" => $plain,
    ];
}

function sendClientReservationStatusEmail(string $clienteEmail, string $clienteNombre, string $detalle, string $estado): bool
{
    $email = buildClientReservationStatusEmail($clienteEmail, $clienteNombre, $detalle, $estado);
    return sendEmail($email["to"], $email["subject"], $email["html"], $email["plain"]);
}

function queueClientReservationStatusEmail(string $clienteEmail, string $clienteNombre, string $detalle, string $estado): bool
{
    $email = buildClientReservationStatusEmail($clienteEmail, $clienteNombre, $detalle, $estado);
    return queueEmail($email["to"], $email["subject"], $email["html"], $email["plain"]);
}
