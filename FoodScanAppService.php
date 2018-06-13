<?php

namespace Lifyzer\Api;

require_once __DIR__ . '/vendor/autoload.php';

require 'config.php';
require 'TableVars.php';
require 'ConstantValues.php';
require 'HelperFunctions.php';
require 'PDOFunctions.php';

$post_body = file_get_contents('php://input');
$post_body = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($post_body));
$postData = json_decode($post_body);

$logger = new Logger();
if (DEBUG_MODE) {
    $logger->showErrors();
} else {
    $logger->hideErrors();
}
unset($logger);

switch ($_REQUEST['Service']) {
    /*********************  User Functions *********************/
    case User::REGISTRATION_ACTION:
    case User::LOGIN_ACTION:
    case User::CHANGE_PASSWORD_ACTION:
    case User::EDIT_PROFILE_ACTION:
    case User::FORGOT_PASSWORD_ACTION:
    case User::DELETE_ACCOUNT_ACTION:
    case User::DATA_TAKEOUT:
        $access_key = validateObject($postData, 'access_key', '');
        $access_key = addslashes($access_key);

        $secret_key = validateObject($postData, 'secret_key', '');
        $secret_key = addslashes($secret_key);

        $connection = $GLOBALS['con'];
        $isSecure = (new Security($connection))->checkForSecurityNew($access_key, $secret_key);
        if ($isSecure === NO) {
            $data['status'] = FAILED;
            $data['message'] = MALICIOUS_SOURCE;
        } elseif ($isSecure === ERROR) {
            $data['status'] = FAILED;
            $data['message'] = TOKEN_ERROR;
        } else {
            $user = new User($connection);
            $data = $user->callService($_REQUEST['Service'], $postData);

            if ($isSecure !== YES || $isSecure !== YES) {
                if ($isSecure['key'] == 'Temp') {
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
        $access_key = validateObject($postData, 'access_key', '');
        $access_key = addslashes($access_key);

        $secret_key = validateObject($postData, 'secret_key', '');
        $secret_key = addslashes($secret_key);

        $connection = $GLOBALS['con'];
        $isSecure = (new Security($connection))->checkForSecurityNew($access_key, $secret_key);

        if ($isSecure === NO) {
            $data['status'] = FAILED;
            $data['message'] = MALICIOUS_SOURCE;
        } elseif ($isSecure === ERROR) {
            $data['status'] = FAILED;
            $data['message'] = TOKEN_ERROR;
        } else {
            $product = new Product($connection);
            $data = $product->callService($_REQUEST['Service'], $postData);
            if ($isSecure !== YES || $isSecure !== YES) {
                if ($isSecure['key'] === 'Temp') {
                    $data['TempToken'] = $isSecure['value'];
                } else {
                    $data['UserToken'] = $isSecure['value'];
                }
            }
        }
        break;

    case Security::UPDATE_USER_TOKEN:
    case Security::TEST_ENCRYPTION:
    case Security::REFRESH_TOKEN:
        $connection = $GLOBALS['con'];
        $security = new Security($connection);
        $data = $security->callService($_REQUEST['Service'], $postData);
        break;

    default:
        $data['data'] = 'No Service Found';
        $data['message'] = $_REQUEST['Service'];
}

//(new AllowCors)->init(); // Set CORS headers
header('Content-type: application/json');
echo json_encode($data);
$GLOBALS['con'] = null;
