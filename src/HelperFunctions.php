<?php

declare(strict_types=1);

namespace Lifyzer\Api;

const BASE62_CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

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

function validateValue(string $value, string $placeHolder): string
{
    $value = strlen($value) > 0 ? $value : $placeHolder;
    return $value;
}

function validateObject($object, $key, $placeHolder)
{
    if (isset($object->$key)) {
        return $object->$key;
    }

    return $placeHolder;
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
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= BASE62_CHARACTERS[rand(0, strlen(BASE62_CHARACTERS) - 1)];
    }

    return $randomString;
}

function curlRequestLoad($url, $isParam = false, $params = '')
{
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]
    );

    if ($isParam) {
        curl_setopt_array(CURLOPT_POSTFIELDS, $params);
    }

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}
