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
    case UserFunctions::REGISTRATION_ACTION:
    case UserFunctions::LOGIN_ACTION:
    case UserFunctions::CHANGE_PASSWORD_ACTION:
    case UserFunctions::EDIT_PROFILE_ACTION:
    case UserFunctions::FORGOT_PASSWORD_ACTION:
    case UserFunctions::DELETE_ACCOUNT_ACTION:
    case UserFunctions::DATA_TAKEOUT:
        $access_key = validateObject($postData, 'access_key', '');
        $access_key = addslashes($access_key);

        $secret_key = validateObject($postData, 'secret_key', '');
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
            $user = new UserFunctions($connection);
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
        $isSecure = (new SecurityFunctions($connection))->checkForSecurityNew($access_key, $secret_key);

        if ($isSecure === NO) {
            $data['status'] = FAILED;
            $data['message'] = MALICIOUS_SOURCE;
        } elseif ($isSecure === ERROR) {
            $data['status'] = FAILED;
            $data['message'] = TOKEN_ERROR;
        } else {
            $user = new ProductFunctions($connection);
            $data = $user->callService($_REQUEST['Service'], $postData);
            if ($isSecure !== YES || $isSecure !== YES) {
                if ($isSecure['key'] === 'Temp') {
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
$GLOBALS['con'] = null;
