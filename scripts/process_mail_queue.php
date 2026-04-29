<?php

define("MAIL_QUEUE_PROCESSOR_RUNNING", true);

require_once dirname(__DIR__) . "/src/helpers/mail.php";

ignore_user_abort(true);

if (function_exists("set_time_limit")) {
    @set_time_limit(0);
}

processQueuedEmails();
