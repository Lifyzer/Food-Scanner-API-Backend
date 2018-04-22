<?php

// print array with format
function pr($arr = null, $exit = 1, $append_text = null) {
    if ($arr != null) {
        echo "<pre>";
        if ($arr != null)
            echo $append_text;

        print_r($arr);

        if ($exit == 1)
            exit;
    }
}

function errorLogFunction($error_message) {
    $log_file = date("F_j_Y").'_log.txt';
    $file = 'error_log_' . date("Ymd") . '.txt';
    $current = @file_get_contents($file);
    $current = "\n----------------------------\n";
    $current .= basename(__FILE__) .'/LogFile/'. "\n----------------------------\n";
    $current .= "Date := " . date("Y-m-d H:i:s") . "\n----------------------------\n";
    $current.=$error_message;
    $current .= (microtime(true)) - time() . " seconds elapsed\n\n";
    // Write the contents back to the file
    file_put_contents(dirname(__FILE__) . '/LogFile/' . $file, $current, FILE_APPEND);
}

 function validateValue($value, $placeHolder) {
    $value = strlen($value) > 0 ? $value : $placeHolder;
    return $value;
}

function validateObject($object, $key, $placeHolder) {

    if (isset($object->$key)) {
        // $value = validateValue($object->$key, "");
        return $object->$key;
    }

    return $placeHolder;
}

function json_validate($string) {
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

function generatePassword($password)
{
    $cost = 10;

    $saltPassword = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
    $saltPassword = sprintf("$2a$%02d$", $cost). $saltPassword;

    $finalHashPassword = crypt($password, $saltPassword);

    return $finalHashPassword;
}

function matchPassword($userPassword, $dbPassword)
{
    if (crypt($userPassword, $dbPassword) === $dbPassword) {
        return 1;
    }

    return 0;
}

function matchStringValue($str1, $str2)
{
    if (strcmp($str1, $str2)) {
        return 1;
    }

    return 0;
}

function encryptPassword( $str ) {
    return md5($str);
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

function generatecoupontring($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}


function generateRandomCode($length)
{
    $numbers = range('0','9');
    $final_array = array_merge($numbers);
    while($length--) {
        $key = array_rand($final_array);
        $randomString = $final_array[$key];
    }

    return $randomString;
}

function validateFormdata($object, $key, $placeHolder)
{

    if (isset($object[$key])) {
//        $value = validateValue($object->$key, "");
        return $object[$key];
    }

    return $placeHolder;
}
