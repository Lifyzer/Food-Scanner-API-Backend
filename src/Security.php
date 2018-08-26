<?php

namespace Lifyzer\Api;

use PDO;

class Security
{
    public const REFRESH_TOKEN = 'refreshToken';
    public const TEST_ENCRYPTION = 'testEncryption';
    public const UPDATE_USER_TOKEN = 'updateTokenForUser';
    public const EXPIRED_ALL_USER_TOKEN = 'expiredAllTokenofUser';

    /** @var PDO */
    protected $connection;

    public function __construct(PDO $con)
    {
        $this->connection = $con;
    }

    public function callService($service, $postData)
    {
        switch ($service) {
            case self::REFRESH_TOKEN:
                return $this->refreshToken($postData);

            case self::TEST_ENCRYPTION:
                return $this->testEncryption($postData);

            case self::UPDATE_USER_TOKEN:
                return $this->updateTokenForUser($postData);

            case self::EXPIRED_ALL_USER_TOKEN:
                return $this->expiredAllTokenOfUser($postData);

            default:
                return null;
        }
    }

    //========== Generate Random Unique Token Number ==========

    public function crypto_random_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int)($log / 8) + 1; // length in bytes
        $bits = (int)$log + 1; // length in bits
        $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);

        return $min + $rnd;
    }

    public function updateTokenForUser($userData)
    {

        $connection = $this->connection;
        //$user_id = validateValue($userData->userId, '');

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        if ($user_id != '') {
            $modifiedDate = date(DATETIME_FORMAT, time());
            $generateToken = $this->generateToken(8);
            $objExpiryDate = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'expiry_duration', 'is_delete' => DELETE_STATUS::NOT_DELETE]);
            if (!empty($objExpiryDate)) {
                $expiryDuration = $objExpiryDate['config_value'];
                $currentDate = date("dmyHis", time() + $expiryDuration);
                $token_array = [':userid' => $user_id, ':token' => $generateToken,
                    ':expiry' => $currentDate, ':token1' => $generateToken, ':expiry1' => $currentDate, ':created_date' => $modifiedDate];
                error_reporting(E_ALL & ~E_NOTICE);
                $insertUpdateQuery = "INSERT INTO " . TABLE_APP_TOKENS . " (userid,token,expiry) VALUES(:userid,:token,:expiry)
            ON DUPLICATE KEY UPDATE token = :token1 , expiry = :expiry1, created_date = :created_date";

                if ($stmt = $connection->prepare($insertUpdateQuery)) {
                    if ($stmt->execute($token_array)) {
                        $stmt->closeCursor();

                        $uuid = validateObject($userData, 'GUID', "");
                        $uuid = addslashes($uuid);

                        $security = new ApiCrypter();

                        $objGlobalPassword = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'globalPassword', 'is_delete' => DELETE_STATUS::NOT_DELETE]);

                        if (!empty($objGlobalPassword)) {
                            $masterKey = $objGlobalPassword['config_value'];
                            $data['GUID'] = $userData->GUID;
                            $data['masterKey'] = $masterKey;
                            $data['acessKey'] = $security->encrypt($uuid, $masterKey);
                        }
                        $generateTokenEncrypted = $security->encrypt($generateToken, $uuid);
                        $currentDateEncrypted = $security->encrypt($currentDate, $uuid);
                        $encryptedTokenName = $generateTokenEncrypted . '_' . $currentDateEncrypted;//$security->encrypt($mixedToken, $uuid."_".$username);
                        $data[USERTOKEN] = $encryptedTokenName;
                        $data['status'] = SUCCESS;
                        return $data;
                    } else {
                        $data['status'] = FAILED;
                        $data[USERTOKEN] = NO;
                        return $data;
                    }
                } else {
                    $data['status'] = FAILED;
                    $data[USERTOKEN] = NO;
                    return $data;
                }
            } else {
                $data[STATUS_KEY] = FAILED;
                $data[USERTOKEN] = NO;
                return $data;
            }
        }
        $data[STATUS_KEY] = FAILED;
        $data[USERTOKEN] = NO;

//        print_r($data);

        return $data;
    }


    public function updateTokenForUser_Login($userData)
    {
        $connection = $this->connection;
        $user_id = validateValue($userData->userId, '');

        if ($user_id != '') {
            $modifiedDate = date(DATETIME_FORMAT, time());
            $generateToken = $this->generateToken(8);
            $objExpiryDate = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'expiry_duration', 'is_delete' => DELETE_STATUS::NOT_DELETE]);
            if (!empty($objExpiryDate)) {
                $expiryDuration = $objExpiryDate['config_value'];
                $currentDate = date("dmyHis", time() + $expiryDuration);
                $token_array = [':userid' => $user_id, ':token' => $generateToken,
                    ':expiry' => $currentDate, ':token1' => $generateToken, ':expiry1' => $currentDate, ':created_date' => $modifiedDate];
                error_reporting(E_ALL & ~E_NOTICE);
                $insertUpdateQuery = "INSERT INTO " . TABLE_APP_TOKENS . " (userid,token,expiry) VALUES(:userid,:token,:expiry)
            ON DUPLICATE KEY UPDATE token = :token1 , expiry = :expiry1, created_date = :created_date";

                if ($stmt = $connection->prepare($insertUpdateQuery)) {
                    if ($stmt->execute($token_array)) {
                        $stmt->closeCursor();

                        $uuid = validateValue($userData->GUID, '');

//                      $uuid = validateObject($userData, 'GUID', "");
//                      $uuid = addslashes($uuid);

                        $security = new ApiCrypter();

                        $objGlobalPassword = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'globalPassword', 'is_delete' => DELETE_STATUS::NOT_DELETE]);

                        if (!empty($objGlobalPassword)) {
                            $masterKey = $objGlobalPassword['config_value'];
                            $data['GUID'] = $userData->GUID;
                            $data['masterKey'] = $masterKey;
                            $data['acessKey'] = $security->encrypt($uuid, $masterKey);
                        }
                        $generateTokenEncrypted = $security->encrypt($generateToken, $uuid);
                        $currentDateEncrypted = $security->encrypt($currentDate, $uuid);
                        $encryptedTokenName = $generateTokenEncrypted . '_' . $currentDateEncrypted;//$security->encrypt($mixedToken, $uuid."_".$username);
                        $data[USERTOKEN] = $encryptedTokenName;
                        $data['status'] = SUCCESS;
                        return $data;
                    } else {
                        $data['status'] = FAILED;
                        $data[USERTOKEN] = NO;
                        return $data;
                    }
                } else {
                    $data['status'] = FAILED;
                    $data[USERTOKEN] = NO;
                    return $data;
                }
            } else {
                $data[STATUS_KEY] = FAILED;
                $data[USERTOKEN] = NO;
                return $data;
            }
        }
        $data[STATUS_KEY] = FAILED;
        $data[USERTOKEN] = NO;

//        print_r($data);

        return $data;
    }

    public function generateUniqueId()
    {
        // return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        //Remove last 4 charcter from above string to make string of 32 characters long.
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function checkForSecurityNew($accessValue, $secretValue)
    {
        $connection = $this->connection;
        if ($accessValue == "" || $secretValue == "") {
            return ERROR;
        } else {
            // get user-agent from database
            $objUserAgent = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'userAgent', 'is_delete' => DELETE_STATUS::NOT_DELETE]);

            if (!empty($objUserAgent)) {
                $user_agent = $objUserAgent['config_value'];
                $separateKey = (explode(',', $user_agent));

                // check user-agent is valid
                if ((strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[0]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[1]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[2]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[3]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[4]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[5]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[6]) !== false)) {
                    // get temporary token for user.

                    $getTempToken = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'tempToken', 'is_delete' => DELETE_STATUS::NOT_DELETE]);
                    if (!empty($getTempToken)) {
                        $tempToken = $getTempToken['config_value'];
                        $objGlobalPassword = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'globalPassword', 'is_delete' => DELETE_STATUS::NOT_DELETE]);
                        if (!empty($objGlobalPassword)) {
                            $masterKey = $objGlobalPassword['config_value'];
                            $security = new ApiCrypter();
                            if ($accessValue === 'nousername') {
                                // check user passed temporary token or request with temporary token.
                                if ($secretValue == null) {
                                    $secretValue = $security->encrypt($tempToken, $masterKey);
                                    $response = [];
                                    $response['key'] = "Temp";// return temporary token
                                    $response['value'] = $secretValue;
                                    return $response;
                                } else {
                                    /*echo "\n temp=>".$tempToken;
                                    echo "\n master=>".$masterKey;
                                    echo "\n secret=>".$secretValue;
                                    echo "\n new serc==> ". $secretValue1 */
                                    $secretValue1 = $security->encrypt($tempToken, $masterKey);
                                    if (trim($secretValue1) == trim($secretValue)) {
                                        return YES;
                                    } else {
                                        return NO;
                                    }
                                }
                            } else {
//                                echo "\nacces=>".$accessValue;
//                                echo "\nsec=>".$secretValue;
                                $tempToken = $security->encrypt($tempToken, $masterKey);
                                return $this->checkCredentialsForSecurityNew($accessValue, $secretValue, $tempToken);
                            }
                        }
                    }
                }
            }
        }

        return NO;
    }

    public function checkCredentialsForSecurityNew($accessValue, $secretValue, $tempToken)
    {
        $connection = $this->connection;
        $objGlobalPassword = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'globalPassword', 'is_delete' => DELETE_STATUS::NOT_DELETE]);
        if (!empty($objGlobalPassword)) {
            $masterKey = $objGlobalPassword['config_value'];
            $security = new ApiCrypter();
            $decrypted_access_key = $security->decrypt($accessValue, $masterKey);
            $objUser = getSingleTableData($connection, TABLE_USER, "", "id", "", ['guid' => $decrypted_access_key, 'is_delete' => DELETE_STATUS::NOT_DELETE]);
            if (!empty($objUser)) {
                $row_token = getSingleTableDataLastDate(
                    $connection,
                    TABLE_APP_TOKENS,
                    '',
                    'token,expiry',
                    '',
                    ['userid' => $objUser['id'], 'is_delete' => DELETE_STATUS::NOT_DELETE]
                );

                if (!empty($row_token)) {
                    $tokenName = $row_token['token'];
                    $currentDate = $row_token['expiry'];
                    if ($secretValue == $tempToken) {
                        // we can return user's private access token here
                        // $tokenName = $tokenName."_".$currentDate;
                        $currentDateEncrypt = $security->encrypt($currentDate, $decrypted_access_key);
                        $tokenNameEncrypt = $security->encrypt($tokenName, $decrypted_access_key);
//                                                 echo ' current date encrpt=> '.$currentDateEncrypt;
//                                                 echo ' token name encrpt=> '.$tokenNameEncrypt;
                        $tokenName = $tokenNameEncrypt . '_' . $currentDateEncrypt;
                        $response = [];
                        $response['key'] = 'User'; // return user's private token
                        $response['value'] = $tokenName;

                        // echo ' secret=access scenario my token=> '.$tokenName;
                        return $response;
                    } elseif ($secretValue === null) {
                        $currentDateEncrypt = $security->encrypt($currentDate, $decrypted_access_key);
                        $tokenNameEncrypt = $security->encrypt($tokenName, $decrypted_access_key);
                        $tokenName = $tokenNameEncrypt . '_' . $currentDateEncrypt;
                        $response = [];
                        $response['key'] = "User";// return user's private token
                        $response['value'] = $tokenName;
                        return $response;
                    } else {
                        $secretValue = explode('_', $secretValue);
                        $decrypted_secret_key = $security->decrypt($secretValue[0], $decrypted_access_key);
//                                                echo $decrypted_secret_key;
//                                                $decrypted_secret_key1 = $security->decrypt($secretValue[1], $decrypted_access_key);
//                                                echo $decrypted_secret_key1;
//                                                echo $tokenName;
                        if ($decrypted_secret_key == $tokenName) {
                            return YES;
                        } else {
                            return NO;
                        }
                    }
                } else {
                    return NO;
                }
            } else {
                return NO;
            }
        }
        return NO;
    }

    public function checkForSecurityForRefreshToken($accessValue, $secretValue)
    {
        $connection = $this->connection;
        if ($accessValue == "") {
            $data[STATUS_KEY] = FAILED;
            $data[MESSAGE_KEY] = TOKEN_ERROR;
        } else {
            $objUserAgent = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'userAgent', 'is_delete' => DELETE_STATUS::NOT_DELETE]);
            if (!empty($objUserAgent)) {
                $user_agent = $objUserAgent['config_value'];
                $separateKey = explode(',', $user_agent);
                // check user-agent is valid
                if ((strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[0]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[1]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[2]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[3]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[4]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[5]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[6]) !== false)) {
                    // get temporary token for user.

                    $getTempToken = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'tempToken', 'is_delete' => DELETE_STATUS::NOT_DELETE]);
                    if (!empty($getTempToken)) {
                        $tempToken = $getTempToken['config_value'];
                        $objGlobalPassword = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", ['config_key' => 'globalPassword', 'is_delete' => DELETE_STATUS::NOT_DELETE]);
                        if (!empty($objGlobalPassword)) {
                            $masterKey = $objGlobalPassword['config_value'];
                            $security = new ApiCrypter();
                            if ($accessValue === 'nousername') {
                                // check user passed temporary token or request with temporary token.

                                if ($secretValue == null) {
//                                    echo "\n temp=>".$tempToken;
//                                    echo "\n master=>".$masterKey;
//                                    echo "\n new serc==> ".
                                    $secretValue = $security->encrypt($tempToken, $masterKey);
                                    $response = [];
                                    $response['key'] = "Temp";// return temporary token
                                    $response['value'] = $secretValue;
                                    return $response;
                                } else {
                                    $secretValue = $security->decrypt($secretValue, $masterKey);
                                    // match token is valid or not
                                    if ($secretValue == $tempToken) {
                                        return YES;
                                    }

                                    return NO;
                                }
                            } else {
                                $tempToken = $security->encrypt($tempToken, $masterKey);
                                return $this->checkCredentialsForSecurityNew($accessValue, $secretValue, $tempToken);
                            }
                        }
                    }
                }
            }
        }

        return NO;
    }

    public function getAdminConfigWithToken($postData)
    {
        $data = [];
        $connection = $this->connection;
        $secret_key = validateObject($postData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $access_key = validateObject($postData, 'access_key', "");
        $access_key = addslashes($access_key);
        if ($access_key == "") {
            $data[STATUS_KEY] = FAILED;
            $data[MESSAGE_KEY] = TOKEN_ERROR;
        } else {
            $isSecure = $this->checkForSecurityNew($access_key, $secret_key);
            if ($isSecure != NO) {
                $stmt_get_admin_config = getMultipleTableData(
                    $connection,
                    TABLE_ADMIN_CONFIG,
                    '',
                    'config_key,config_value',
                    " config_key IN('globalPassword','userAgent','tempToken')",
                    ['is_delete' => DELETE_STATUS::NOT_DELETE]
                );

                if ($stmt_get_admin_config->rowCount() > 0) {
                    while ($objAdminConfig = $stmt_get_admin_config->fetch(PDO::FETCH_ASSOC)) {
                        $data[$objAdminConfig['config_key']] = $objAdminConfig['config_value'];
                    }
                }
                $stmt_get_admin_config->closeCursor();
            } else {
                $data = '';
            }
        }

        return $data;
    }

    public function getUserAgent()
    {
        $string = $_SERVER ['HTTP_USER_AGENT'];
        $data['User_agent'] = $string;

        return $data;
    }

    private function generateToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_random_secure(0, $max)];
        }

        return $token;
    }

    private function refreshToken($userData)
    {
        $access_key = validateObject($userData, 'access_key', "");
        $access_key = addslashes($access_key);

        $isSecure = $this->checkForSecurityForRefreshToken($access_key, "");

        if ($isSecure == NO) {
            $status = FAILED;
            $message = MALICIOUS_SOURCE;
        } elseif ($isSecure == ERROR) {
            $status = FAILED;
            $message = TOKEN_ERROR;
        } else {
            //print_r($isSecure);
            if ($isSecure != YES) {
                if ($isSecure['key'] == "Temp") {
                    $data['data']['tempToken'] = $isSecure['value'];
                } else {
                    $data['data']['userToken'] = $isSecure['value'];
                }
            }
            $status = SUCCESS;
            $message = "Token is generated.";
        }

        $data[STATUS_KEY] = $status;
        $data[MESSAGE_KEY] = $message;

        $arr_adminconfig = $this->getAdminConfigWithToken($userData);
        $arr_adminconfig['key_iv'] = ENCRYPTION_KEY_IV;
        $data['data']['adminConfig'] = $arr_adminconfig;

        return $data;
    }

    private function test($userData)
    {
        $plaintext = validateValue($userData->guid, "");
        $key = "_$(Skill)!_square@#$%_23_06_2017";
        //$key previously generated safely, ie: openssl_random_pseudo_bytes
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);

        //decrypt later....
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
        {
            $decode = $original_plaintext;
        }
        $data['encode'] = $ciphertext;
        $data['decode'] = $decode;

        return $data;
    }

    private function testEncryption($userData)
    {
        $guid = validateValue($userData->guid, "");
        $global_pwd_value = "_$(Skill)!_square@#$%_23_06_2017";
        $security = new ApiCrypter();
        $encrpt_acesskey = $security->encrypt($guid, $global_pwd_value);
        $data['encrypted_value'] = $encrpt_acesskey;
        $data['decrypted_value'] = $security->decrypt($encrpt_acesskey, $global_pwd_value);

        return $data;
    }

    private function expiredAllTokenOfUser($userData)
    {
        $user_id = validateValue($userData['userId'], '');

        if ($user_id != '') {
            $modifiedDate = date(DATETIME_FORMAT, time());
            editData($this->connection, 'ExpireToken', TABLE_APP_TOKENS, ['modified_date' => $modifiedDate], ['userid' => $user_id], "");

            return YES;
        }

        return NO;
    }
}
