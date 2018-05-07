<?php

use Dotenv\Dotenv;
use phpFastCache\CacheManager;

// Setup cache config
CacheManager::setDefaultConfig([
    'path' => __DIR__ . '/cache',
]);

(new Dotenv(__DIR__))->load();

// First, convert "true/false" string from phpdotenv to boolean
$debugMode = filter_var(getenv('DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN);
$cacheStatus = filter_var(getenv('CACHE'), FILTER_VALIDATE_BOOLEAN);
define('DEBUG_MODE', $debugMode);
define('CACHE_ENABLED', $cacheStatus);


date_default_timezone_set('UTC');
$server = getenv('DB_HOST');
$user = getenv('DB_USER');
$password = getenv('DB_PWD');
$dbname = getenv('DB_NAME');

global $con;

try {
    $con = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8mb4", $user, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $con->exec('SET NAMES UTF8');
} catch(PDOException $e) {
    if (DEBUG_MODE) {
        echo $e->getMessage();
    }
}
