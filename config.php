<?php

namespace Lifyzer\Api;

use Dotenv\Dotenv;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

$requiredEnvFields = [
    'DB_HOST',
    'DB_USER',
    'DB_PWD',
    'DB_NAME',
    'DEBUG_MODE',
    'CACHE',
    'ENCRYPTION_KEY_IV',
    'SENDER_EMAIL_NAME',
    'SENDER_EMAIL_ID',
    'SENDER_EMAIL_PASSWORD',
    'SWISS_FOOD_KEY',
    'USA_FOOD_KEY',
    'URL_OPEN_FOOD_NAME_API',
    'URL_OPEN_FOOD_BARCODE_API',
    'URL_USA_FOOD_API',
    'URL_SWISS_FOOD_API'
];

// Setup cache config
CacheManager::setDefaultConfig(
    new ConfigurationOption(
        [
            'path' => dirname(__DIR__) . '/cache'
        ]
    )
);

$env = Dotenv::createImmutable(__DIR__);
$env->load();
$env->required($requiredEnvFields)->notEmpty();

// First, convert "true/false" string from phpdotenv to boolean
$debugMode = filter_var(getenv('DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN);
$cacheStatus = filter_var(getenv('CACHE'), FILTER_VALIDATE_BOOLEAN);
define('DEBUG_MODE', $debugMode);
define('CACHE_ENABLED', $cacheStatus);

date_default_timezone_set('UTC');
