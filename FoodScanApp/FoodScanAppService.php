<?php

include_once 'config.php';
include_once 'HelperFunctions.php';
include_once 'TableVars.php';
include_once 'ConstantValues.php';
include_once 'SecurityFunctions.php';
include_once 'PDOFunctions.php';

$post_body = file_get_contents('php://input');
$post_body = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($post_body));
$reqData[] = json_decode($post_body);
error_reporting(0);
$postData = $reqData[0];

$debug = 0;
$logger->Log($debug, 'POST DATA :', $postData);
$status = "";
$logger->Log($debug, 'Service :', $_REQUEST['Service']);

switch ($_REQUEST['Service']) {
    /*********************  User Functions ******************************/

    case "Registration":
    case "Login":
    case "ChangePassword":
    case "EditProfile":
    case "ForgotPassword":
    {
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
            $data = $user->call_service($_REQUEST['Service'], $postData);

            if ($isSecure != 'yes' || $isSecure != 'yes') {
                if ($isSecure['key'] == "Temp") {
                    $data['TempToken'] = $isSecure['value'];
                } else {
                    $data['UserToken'] = $isSecure['value'];
                }
            }
        }
    }
        break;

    case "addToFavourite":
    case "getAllUserFavourite":
    case "getProductDetails":
    case "getUserHistory":
    case "removeProductFromHistory":
    {
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
            include_once 'ProductFunctions.php';
            $user = new ProductFunctions($connection);
            $data = $user->call_service($_REQUEST['Service'], $postData);
            if ($isSecure != 'yes' || $isSecure != 'yes') {
                if ($isSecure['key'] == "Temp") {
                    $data['TempToken'] = $isSecure['value'];
                } else {
                    $data['UserToken'] = $isSecure['value'];
                }
            }
        }
    }
        break;

    case "updateTokenForUser":
    case "testEncryption":
    case "refreshToken": {
        $connection = $GLOBALS['con'];
        $security = new SecurityFunctions($connection);
        $data = $security->call_service($_REQUEST['Service'], $postData);
    }
        break;

    default: {
        $data['data'] = 'No Service Found';
        $data['message'] = $_REQUEST['Service'];
    }
        break;
}

//(new AllowCors)->init(); // Set CORS headers
header('Content-type: application/json');
echo json_encode($data);
$GLOBALS['con']=null;
