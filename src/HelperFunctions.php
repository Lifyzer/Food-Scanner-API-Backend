<?php

declare(strict_types=1);

namespace Lifyzer\Api;

function errorLogFunction(string $errorMessage): void
{
    $file = date('F-j-Y') . '_log.txt';
    $current = @file_get_contents($file);
    $current .= "\n----------------------------\n";
    $current .= basename(dirname(__DIR__)) . '/logs/';
    $current .= "\n----------------------------\n";
    $current .= "Date := " . date(DATETIME_FORMAT);
    $current .= "\n----------------------------\n";
    $current .= $errorMessage;
    $current .= (microtime(true)) - time() . " seconds elapsed\n\n";

    // Write the contents back to the file
    file_put_contents(Logger::LOG_PATH . $file, $current, FILE_APPEND);
}

function validateValue($value, $placeHolder)
{
    $value = strlen($value) > 0 ? $value : $placeHolder;
    return $value;
}

function validateObject($object, $key, $placeHolder)
{
    if (isset($object->$key))
        return $object->$key;

    return $placeHolder;
}

function json_validate($string): bool
{
    if (is_string($string)) {
        @json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    return false;
}

function getDefaultDate(): string
{
    return date(DATETIME_FORMAT);
}

function encryptPassword(string $password): string
{
    return sha1($password);
}

function generateRandomString(int $length = 10): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}
