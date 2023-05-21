<?php

namespace Lifyzer\Api;

use PDOException;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Run as WhoopsRun;

require __DIR__ . '/vendor/autoload.php';

$whoops = new WhoopsRun;
$whoops->pushHandler(new JsonResponseHandler);
$whoops->register();

require 'config.php';
require 'src/TableVars.php';
require 'src/ConstantValues.php';
require 'src/HelperFunctions.php';
require 'src/PdoFunctions.php';

$postBody = file_get_contents('php://input');
$postBody = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($postBody));
$postData = json_decode($postBody);

$logger = new Logger();
if (DEBUG_MODE) {
    $logger->showErrors();
} else {
    $logger->hideErrors();
}
unset($logger);

if (!empty($_REQUEST['Service'])) {
    try {
        $db = new Database();
    } catch (PDOException $except) {
        if (DEBUG_MODE) {
            echo $except->getMessage();
        } else {
            exit('An unexpected database error occured.');
        }
    }
    switch ($_REQUEST['Service']) {
        /*** User ***/
        case User::REGISTRATION_ACTION:
        case User::LOGIN_ACTION:
        case User::CHANGE_PASSWORD_ACTION:
        case User::EDIT_PROFILE_ACTION:
        case User::FORGOT_PASSWORD_ACTION:
        case User::DELETE_ACCOUNT_ACTION:
        case User::DATA_TAKEOUT:
        case User::LOGS_ACTION:
            $access_key = validateObject($postData, 'access_key', '');
            $access_key = addslashes($access_key);

            $secret_key = validateObject($postData, 'secret_key', '');
            $secret_key = addslashes($secret_key);

            $isSecure = (new Security($db))->checkForSecurityNew($access_key, $secret_key);
            $isSecure = YES;
            if ($isSecure === NO) {
                $data['status'] = FAILED;
                $data['message'] = MALICIOUS_SOURCE;
            } elseif ($isSecure === ERROR) {
                $data['status'] = FAILED;
                $data['message'] = TOKEN_ERROR;
            } else {
                $user = new User($db);
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

        /*** Food Items & Reviews/Rating ***/
        case 'addToFavourite':
        case 'getAllUserFavourite':
        case 'getProductDetails':
        case 'getUserHistory':
        case 'removeProductFromHistory':
        case 'addReview':
        case 'updateReview':
        case 'deleteReview':
        case 'getReviewList':
        case 'updateRatingStatus':
        case 'getProductDetailsV2':
            $access_key = validateObject($postData, 'access_key', '');
            $access_key = addslashes($access_key);

            $secret_key = validateObject($postData, 'secret_key', '');
            $secret_key = addslashes($secret_key);

            $isSecure = (new Security($db))->checkForSecurityNew($access_key, $secret_key);
            $isSecure = YES;

            if ($isSecure === NO) {
                $data['status'] = FAILED;
                $data['message'] = MALICIOUS_SOURCE;
            } elseif ($isSecure === ERROR) {
                $data['status'] = FAILED;
                $data['message'] = TOKEN_ERROR;
            } else {
                $product = new Product($db);
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

        /*** Security ***/
        case Security::UPDATE_USER_TOKEN:
        case Security::TEST_ENCRYPTION:
        case Security::REFRESH_TOKEN:
            $security = new Security($db);
            $data = $security->callService($_REQUEST['Service'], $postData);
            break;
        default:
            $data['data'] = 'No Service Found';
            $data['message'] = $_REQUEST['Service'];
    }
}

// (new AllowCors)->init(); // Set CORS headers
header('Content-type: application/json');
echo json_encode($data);
