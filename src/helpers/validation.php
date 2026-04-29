<?php

function validateText($value, $min = 1, $max = 255)
{
    $value = trim((string) $value);

    if ($value === "") {
        return null;
    }

    $length = function_exists("mb_strlen") ? mb_strlen($value) : strlen($value);

    if ($length < $min || $length > $max) {
        return null;
    }

    return $value;
}

function validateEmailAddress($value)
{
    $email = trim(strtolower((string) $value));

    if ($email === "") {
        return null;
    }

    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function validatePositiveInt($value, $min = 1, $max = null)
{
    $intValue = filter_var($value, FILTER_VALIDATE_INT);

    if ($intValue === false || $intValue < $min) {
        return null;
    }

    if ($max !== null && $intValue > $max) {
        return null;
    }

    return (int) $intValue;
}

function validatePositiveFloat($value, $min = 0.01, $max = null)
{
    if (!is_numeric($value)) {
        return null;
    }

    $floatValue = (float) $value;

    if ($floatValue < $min) {
        return null;
    }

    if ($max !== null && $floatValue > $max) {
        return null;
    }

    return $floatValue;
}

function validateEnumValue($value, $allowedValues)
{
    $value = trim((string) $value);

    return in_array($value, $allowedValues, true) ? $value : null;
}

function validateDateValue($value)
{
    $value = trim((string) $value);
    $date = DateTime::createFromFormat("Y-m-d", $value);
    $errors = DateTime::getLastErrors();

    if (!$date) {
        return null;
    }

    if ($errors !== false && (($errors["warning_count"] ?? 0) > 0 || ($errors["error_count"] ?? 0) > 0)) {
        return null;
    }

    return $date->format("Y-m-d") === $value ? $value : null;
}

function validateTimeValue($value)
{
    $value = trim((string) $value);
    $time = DateTime::createFromFormat("H:i", $value);
    $errors = DateTime::getLastErrors();

    if (!$time) {
        return null;
    }

    if ($errors !== false && (($errors["warning_count"] ?? 0) > 0 || ($errors["error_count"] ?? 0) > 0)) {
        return null;
    }

    return $time->format("H:i") === $value ? $value : null;
}

function isDateOnOrAfterToday($date)
{
    $today = new DateTimeImmutable("today");
    $targetDate = new DateTimeImmutable($date);

    return $targetDate >= $today;
}

function isDateRangeValid($startDate, $endDate)
{
    return $endDate > $startDate;
}

function isDateTimeInFuture($date, $time)
{
    $reservationDateTime = new DateTimeImmutable($date . " " . $time);
    $now = new DateTimeImmutable("now");
    $now = $now->setTime((int) $now->format("H"), (int) $now->format("i"));

    return $reservationDateTime >= $now;
}
