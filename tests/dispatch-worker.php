<?php
// Offline doubles replace I/O only; the real entrypoint dispatch code is evaluated below.
namespace Whoops\Handler { class JsonResponseHandler {} }
namespace Whoops { class Run { public function pushHandler($handler) {} public function register() {} } }
namespace Lifyzer\Api {
    class Database {}
    class Logger { public function hideErrors() {} }
    class Security {
        const UPDATE_USER_TOKEN = 'updateTokenForUser';
        const TEST_ENCRYPTION = 'testEncryption';
        const REFRESH_TOKEN = 'refreshToken';
        public function __construct($db) {}
        public function checkForSecurityNew($access, $secret) { return $GLOBALS['result']; }
    }
    class User {
        const REGISTRATION_ACTION = 'Registration';
        const LOGIN_ACTION = 'Login';
        const CHANGE_PASSWORD_ACTION = 'ChangePassword';
        const EDIT_PROFILE_ACTION = 'EditProfile';
        const FORGOT_PASSWORD_ACTION = 'ForgotPassword';
        const DELETE_ACCOUNT_ACTION = 'DeleteAccount';
        const DATA_TAKEOUT = 'TakeOut';
        const LOGS_ACTION = 'AddLogs';
        public function __construct($db) {}
        public function callService($service, $data) { return ['status' => 'called', 'text' => $data->text]; }
    }
    class Product extends User {}
    function file_get_contents($path) { return $GLOBALS['body']; }
    function header($value) {}
    function validateObject($data, $key, $default) { return $data->$key ?? $default; }
    require dirname(__DIR__) . '/src/ConstantValues.php';
    define('DEBUG_MODE', false);
    $case = json_decode(base64_decode($argv[1]), true);
    $GLOBALS['result'] = $case['result'];
    $GLOBALS['body'] = $case['body'];
    $_REQUEST['Service'] = $case['service'];
    $source = \file_get_contents(dirname(__DIR__) . '/FoodScanAppService.php');
    // Skip only bootstrap requires, which would otherwise load real database/provider dependencies.
    $source = preg_replace('/^require [^\r\n]+;\r?$/m', '', $source);
    eval(substr($source, 5));
}
