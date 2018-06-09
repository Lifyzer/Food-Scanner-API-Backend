<?php

namespace Lifyzer\Api;

function errorLogFunction($error_message)
{
    $log_file = date("F_j_Y") . '_log.txt';
    $file = 'error_log_' . date("Ymd") . '.txt';
    $current = @file_get_contents($file);
    $current = "\n----------------------------\n";
    $current .= basename(__FILE__) . '/logs/' . "\n----------------------------\n";
    $current .= "Date := " . date("Y-m-d H:i:s") . "\n----------------------------\n";
    $current .= $error_message;
    $current .= (microtime(true)) - time() . " seconds elapsed\n\n";
    // Write the contents back to the file
    file_put_contents(__DIR__ . '/logs/' . $file, $current, FILE_APPEND);
}

function validateValue($value, $placeHolder)
{
    $value = strlen($value) > 0 ? $value : $placeHolder;
    return $value;
}

function validateObject($object, $key, $placeHolder)
{
    if (isset($object->$key)) {
        // $value = validateValue($object->$key, "");
        return $object->$key;
    }

    return $placeHolder;
}

function json_validate($string)
{
    if (is_string($string)) {
        @json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    return false;
}

function getDefaultDate()
{
    return date('Y-m-d H:i:s');
}

function encryptPassword($str)
{
    return sha1($str);
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}
