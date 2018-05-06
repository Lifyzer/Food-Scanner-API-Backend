<?php

include_once 'Logger.php';

// First, convert "true/false" string from phpdotenv to boolean
$debugMode = filter_var(getenv('DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN);
define('DEBUG_MODE', $debugMode);

$logger = new Logger();
date_default_timezone_set('UTC');
$server = getenv('DB_HOST');
$user = getenv('DB_USER');
$password = getenv('DB_PWD');
$dbname = getenv('DB_NAME');

global $con;

try {
    # MS SQL Server and Sybase with PDO_DBLIB
    # MySQL with PDO_MYSQL
    $con = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8mb4", $user, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //file_put_contents('LogFile/error.txt', $con);
    $con->exec('SET NAMES UTF8');
}
catch(PDOException $e) {
    if (DEBUG_MODE) {
        echo $e->getMessage();
    }
}
