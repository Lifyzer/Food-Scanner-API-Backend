<?php

namespace Lifyzer\Api;

use Dotenv\Dotenv;
use phpFastCache\CacheManager;

// Setup cache config
CacheManager::setDefaultConfig([
    'path' => dirname(__DIR__) . '/cache',
]);

(new Dotenv(__DIR__))->load();

// First, convert "true/false" string from phpdotenv to boolean
$debugMode = filter_var(getenv('DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN);
$cacheStatus = filter_var(getenv('CACHE'), FILTER_VALIDATE_BOOLEAN);
define('DEBUG_MODE', $debugMode);
define('CACHE_ENABLED', $cacheStatus);

date_default_timezone_set('UTC');
