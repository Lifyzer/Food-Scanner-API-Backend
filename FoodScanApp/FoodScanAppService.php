<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use phpFastCache\CacheManager;

(new Dotenv(__DIR__))->load();

include_once 'config.php';
include_once 'HelperFunctions.php';
include_once 'TableVars.php';
include_once 'ConstantValues.php';
include_once 'SecurityFunctions.php';
include_once 'PDOFunctions.php';

// Setup cache config
CacheManager::setDefaultConfig([
    'path' => __DIR__ . '/cache',
]);

$post_body = file_get_contents('php://input');
$post_body = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($post_body));
$postData = json_decode($post_body)[0];

$debug = 0;
$logger->log($debug, 'POST DATA :', $postData);
$status = "";
$logger->log($debug, 'Service :', $_REQUEST['Service']);

switch ($_REQUEST['Service']) {
    /*********************  User Functions ******************************/
    case UserFunctions::REGISTRATION_ACTION:
    case UserFunctions::LOGIN_ACTION:
    case UserFunctions::CHANGE_PASSWORD_ACTION:
    case UserFunctions::EDIT_PROFILE_ACTION:
    case UserFunctions::FORGOT_PASSWORD_ACTION:
    case UserFunctions::DELETE_ACCOUNT_ACTION:
    case UserFunctions::DATA_TAKEOUT:
        $access_key = validateObject($postData, 'access_key', "");
        $access_key = addslashes($access_key);

        $secret_key = validateObject($postData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $connection = $GLOBALS['con'];
        $isSecure = (new SecurityFunctions($connection))->checkForSecurityNew($access_key, $secret_key);
        if ($isSecure == NO) {
            $data['status'] = FAILED;
            $data['message'] = MALICIOUS_SOURCE;
        } elseif ($isSecure == ERROR) {
            $data['status'] = FAILED;
            $data['message'] = TOKEN_ERROR;
        } else {

            include_once 'UserFunctions.php';
            $user = new UserFunctions($connection);
            $data = $user->callService($_REQUEST['Service'], $postData);

            if ($isSecure != 'yes' || $isSecure != 'yes') {
                if ($isSecure['key'] == "Temp") {
                    $data['TempToken'] = $isSecure['value'];
                } else {
                    $data['UserToken'] = $isSecure['value'];
                }
            }
        }
        break;

    case 'addToFavourite':
    case 'getAllUserFavourite':
    case 'getProductDetails':
    case 'getUserHistory':
    case 'removeProductFromHistory':
        $access_key = validateObject($postData, 'access_key', "");
        $access_key = addslashes($access_key);

        $secret_key = validateObject($postData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $connection = $GLOBALS['con'];

        $isSecure = (new SecurityFunctions($connection))->checkForSecurityNew($access_key, $secret_key);

        if ($isSecure === NO) {
            $data['status'] = FAILED;
            $data['message'] = MALICIOUS_SOURCE;
        } elseif ($isSecure === ERROR) {
            $data['status'] = FAILED;
            $data['message'] = TOKEN_ERROR;
        } else {
            include_once 'ProductFunctions.php';
            $user = new ProductFunctions($connection);
            $data = $user->callService($_REQUEST['Service'], $postData);
            if ($isSecure != 'yes' || $isSecure != 'yes') {
                if ($isSecure['key'] == "Temp") {
                    $data['TempToken'] = $isSecure['value'];
                } else {
                    $data['UserToken'] = $isSecure['value'];
                }
            }
        }
        break;

    case SecurityFunctions::UPDATE_USER_TOKEN:
    case SecurityFunctions::TEST_ENCRYPTION:
    case SecurityFunctions::REFRESH_TOKEN:
    case SecurityFunctions::TOKEN_DATA:
        $connection = $GLOBALS['con'];
        $security = new SecurityFunctions($connection);
        $data = $security->callService($_REQUEST['Service'], $postData);
        break;

    default:
        $data['data'] = 'No Service Found';
        $data['message'] = $_REQUEST['Service'];
}

//(new AllowCors)->init(); // Set CORS headers
header('Content-type: application/json');
echo json_encode($data);
$GLOBALS['con']=null;
