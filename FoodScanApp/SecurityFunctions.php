<?php
/**
 * Created by PhpStorm.
 * User: c157
 * Date: 17/01/18
 * Time: 12:19 PM
 */

include_once 'ApiCrypter.php';
include_once 'HelperFunctions.php';
include_once 'ConstantValues.php';
include_once 'TableVars.php';

class SecurityFunctions
{
    protected $connection;
    function __construct(PDO $con)
    {
        $this->connection = $con;
    }

    public function call_service($service, $postData)
    {

        switch ($service) {
            case "refreshToken": {
                return $this->refreshToken($postData);
            }
                break;

            case "testEncryption": {
                return $this->testEncryption($postData);
//                return $this->test($postData);
            }
                break;

            case "updateTokenForUser": {
                return $this->updateTokenForUser($postData);
            }
                break;

            case "expiredAllTokenofUser": {
                return $this->expiredAllTokenofUser($postData);
            }
                break;

            default:
                return null;
                break;
        }
    }

    //============================================== Generate Random Unique Token Number =============================

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

    public function generateToken($length)
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

    // USED METHODS
    public function refreshToken($userData)
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

        $data['data']['adminConfig'] = $this->getAdminConfigWithToken($userData);

        return $data;
    }

    public function getUserAgent()
    {
        $string = $_SERVER ['HTTP_USER_AGENT'];
        $data['User_agent'] = $string;
        return $data;
    }

    function test($userData){

        $plaintext= validateValue($userData->guid, "");
        $key="_$(Skill)!_square@#$%_23_06_2017";
        //$key previously generated safely, ie: openssl_random_pseudo_bytes
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
        $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );

        //decrypt later....
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
        if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
        {
            $decode=$original_plaintext;
        }
        $data['encode']=$ciphertext;
        $data['decode']=$decode;
        return $data;
    }

    public function testEncryption($userData)
    {
        //echo '  Current PHP version: ' . phpversion();
        $guid = validateValue($userData->guid, "");
        $global_pwd_value="_$(Skill)!_square@#$%_23_06_2017";
        $security = new Security();
        $encrpt_acesskey = $security->encrypt($guid, $global_pwd_value);
        $data['encrypted_value'] = $encrpt_acesskey;
        $data['decrypted_value'] = $security->decrypt($encrpt_acesskey, $global_pwd_value);
        return $data;
    }

    public function expiredAllTokenofUser($userData)
    {
        $user_id = validateValue($userData['userId'], '');

        if ($user_id != '') {

            $modifiedDate = date('Y-m-d H:i:s', time());
            editData($this->connection,"ExpireToken",TABLE_APP_TOKENS,array('modified_date'=>$modifiedDate),array('userid'=>$user_id),"");
            return YES;
        }
        return NO;
    }

    // USED METHODS
    public function updateTokenForUser($userData)
    {
        $connection = $this->connection;
        $user_id = validateValue($userData->userId, '');
        if ($user_id != '') {
            $modifiedDate = date('Y-m-d H:i:s', time());
            $generateToken = $this->generateToken(8);
            $objExpiryDate = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", array('config_key' => 'expiry_duration', 'is_delete' => DELETE_STATUS::NOT_DELETE));
            if (!empty($objExpiryDate)) {
                $expiryDuration = $objExpiryDate['config_value'];
                $currentdate = date("dmyHis", time() + $expiryDuration);
                $token_array = array(':userid' => $user_id, ':token' => $generateToken,
                    ':expiry' => $currentdate, ':token1' => $generateToken, ':expiry1' => $currentdate, ':created_date' => $modifiedDate);
                $insertUpdateQuery = "INSERT INTO " . TABLE_APP_TOKENS . " (userid,token,expiry) VALUES(:userid,:token,:expiry)
            ON DUPLICATE KEY UPDATE token = :token1 , expiry = :expiry1, created_date = :created_date";
            if ($stmt = $connection->prepare($insertUpdateQuery)) {
                    if ($stmt->execute($token_array)) {
                        $stmt->closeCursor();
                        $uuid = validateValue($userData->GUID, '');
                        $security = new Security();
                        $objGlobalPassword = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", array('config_key' => 'globalPassword', 'is_delete' => DELETE_STATUS::NOT_DELETE));
                        if (!empty($objGlobalPassword)) {
                            $masterKey = $objGlobalPassword['config_value'];
                            $data['GUID'] = $userData->GUID;
                            $data['masterKey'] = $masterKey;
                            $data['acessKey'] = $security->encrypt($uuid, $masterKey);
                        }
                        $generateTokenEncrypted = $security->encrypt($generateToken, $uuid);
                        $currentdateEncrypted = $security->encrypt($currentdate, $uuid);
                        $encryptedTokenName = $generateTokenEncrypted . "_" . $currentdateEncrypted;//$security->encrypt($mixedToken, $uuid."_".$username);
                        $data[USERTOKEN] = $encryptedTokenName;
                        $data['status'] = SUCCESS;
                        return $data;
                    }
                    else {
                        $data['status'] = FAILED;
                        $data[USERTOKEN] = NO;
                        return $data;
                    }
                }
                else {
                    $data['status'] = FAILED;
                    $data[USERTOKEN] = NO;
                    return $data;
                }
            }
            else {
                $data[STATUS_KEY] = FAILED;
                $data[USERTOKEN] = NO;
                return $data;
            }
        }
        $data[STATUS_KEY] = FAILED;
        $data[USERTOKEN] = NO;
        return $data;
    }

    // USED METHODS
    public function gen_uuid()
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


    // USED METHODS
    public function checkForSecurityNew($accessvalue, $secretvalue)
    {
        $connection = $this->connection;
        if ($accessvalue == "" || $secretvalue == "") {
            return ERROR;
        } else {
            // get user-agent from database
            $objUserAgent=getSingleTableData($connection,TABLE_ADMIN_CONFIG,"","config_value","",array('config_key'=>'userAgent','is_delete'=>DELETE_STATUS::NOT_DELETE));
            if(!empty($objUserAgent)) {
                $user_agent = $objUserAgent['config_value'];
                $separateKey = (explode(',', $user_agent));
                // check user-agent is valid
                if ((strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[0]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[1]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[2]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[3]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[4]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[5]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[6]) !== false)) {
                    // get temporary token for user.

                    $getTempToken=getSingleTableData($connection,TABLE_ADMIN_CONFIG,"","config_value","",array('config_key'=>'tempToken','is_delete'=>DELETE_STATUS::NOT_DELETE));
                    if(!empty($getTempToken)) {
                        $tempToken = $getTempToken['config_value'];
                        $objGlobalPassword = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", array('config_key' => 'globalPassword', 'is_delete' => DELETE_STATUS::NOT_DELETE));
                        if (!empty($objGlobalPassword)) {
                            $masterKey = $objGlobalPassword['config_value'];
                            $security = new Security();
                            if ($accessvalue == "nousername") {
                                // check user passed temporary token or request with temporary token.

                                if ($secretvalue == NULL) {
                                    $secretvalue = $security->encrypt($tempToken, $masterKey);
                                    $response = array();
                                    $response['key'] = "Temp";// return temporary token
                                    $response['value'] = $secretvalue;
                                    return $response;
                                } else {
                                    /*echo "\n temp=>".$tempToken;
                                    echo "\n master=>".$masterKey;
                                    echo "\n secret=>".$secretvalue;
                                    echo "\n new serc==> ". $secretvalue1 */
                                    $secretvalue1= $security->encrypt($tempToken, $masterKey);
                                    if (trim($secretvalue1) == trim($secretvalue)) {
                                        return YES;
                                    } else {
                                        return NO;
                                    }
                                }
                            }
                            else {
//                                echo "\nacces=>".$accessvalue;
//                                echo "\nsec=>".$secretvalue;
                                $tempToken = $security->encrypt($tempToken, $masterKey);
                                return $this->checkCredentialsForSecurityNew($accessvalue, $secretvalue, $tempToken);
                            }
                        }
                    }
                }
            }
        }
        return NO;
    }

    // USED METHODS
    public function checkCredentialsForSecurityNew($accessvalue, $secretvalue, $tempToken)
    {
        $connection = $this->connection;
        $objGlobalPassword = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", array('config_key' => 'globalPassword', 'is_delete' => DELETE_STATUS::NOT_DELETE));
        if (!empty($objGlobalPassword)) {
            $masterKey = $objGlobalPassword['config_value'];
            $security = new Security();
            $decrypted_access_key = $security->decrypt($accessvalue, $masterKey);
            $objUser= getSingleTableData($connection, TABLE_USER, "", "id", "", array('guid' => $decrypted_access_key, 'is_delete' => DELETE_STATUS::NOT_DELETE));
            if(!empty($objUser)){
                $row_token= getSingleTableData($connection, TABLE_APP_TOKENS, "", "token,expiry", "", array('userid' => $objUser['id'], 'is_delete' => DELETE_STATUS::NOT_DELETE));
                if(!empty($row_token)){
                    $tokenName = $row_token['token'];
                    $currentdate = $row_token['expiry'];
                    if ($secretvalue == $tempToken) {
                        // we can return user's private access token here
                        // $tokenName = $tokenName."_".$currentdate;
                        $currentdateEncrypt = $security->encrypt($currentdate, $decrypted_access_key);
                        $tokenNameEncrypt = $security->encrypt($tokenName, $decrypted_access_key);
//                                                 echo ' current date encrpt=> '.$currentdateEncrypt;
//                                                 echo ' token name encrpt=> '.$tokenNameEncrypt;
                        $tokenName = $tokenNameEncrypt . "_" . $currentdateEncrypt;
                        $response = array();
                        $response['key'] = "User"; // return user's private token
                        $response['value'] = $tokenName;

                        // echo ' secret=access scenario my token=> '.$tokenName;
                        return $response;
                    } else if ($secretvalue == NULL) {
                        $currentdateEncrypt = $security->encrypt($currentdate, $decrypted_access_key);
                        $tokenNameEncrypt = $security->encrypt($tokenName, $decrypted_access_key);
                        $tokenName = $tokenNameEncrypt . "_" . $currentdateEncrypt;
                        $response = array();
                        $response['key'] = "User";// return user's private token
                        $response['value'] = $tokenName;
                        return $response;
                    } else {

                        $secretvalue = explode("_", $secretvalue);
                        $decrypted_secret_key = $security->decrypt($secretvalue[0], $decrypted_access_key);
//                                                echo $decrypted_secret_key;
//                                                $decrypted_secret_key1 = $security->decrypt($secretvalue[1], $decrypted_access_key);
//                                                echo $decrypted_secret_key1;
//                                                echo $tokenName;
                        if ($decrypted_secret_key == $tokenName) {
                            return YES;
                        } else {
                            return NO;
                        }
                    }
                }
                else{return NO;}
            }
            else{
                return NO;
            }
        }
        return NO;
    }

    // USED METHODS
    public function checkForSecurityForRefreshToken($accessvalue, $secretvalue)
    {
        $connection = $this->connection;
        if ($accessvalue == "") {
            $data[STATUS_KEY] = FAILED;
            $data[MESSAGE_KEY] = TOKEN_ERROR;
        } else {
            $objUserAgent=getSingleTableData($connection,TABLE_ADMIN_CONFIG,"","config_value","",array('config_key'=>'userAgent','is_delete'=>DELETE_STATUS::NOT_DELETE));
            if(!empty($objUserAgent)) {
                $user_agent = $objUserAgent['config_value'];
                $separateKey = (explode(',', $user_agent));
                // check user-agent is valid
                if ((strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[0]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[1]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[2]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[3]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[4]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[5]) !== false) || (strpos($_SERVER ['HTTP_USER_AGENT'], $separateKey[6]) !== false)) {
                    // get temporary token for user.

                    $getTempToken=getSingleTableData($connection,TABLE_ADMIN_CONFIG,"","config_value","",array('config_key'=>'tempToken','is_delete'=>DELETE_STATUS::NOT_DELETE));
                    if(!empty($getTempToken)) {
                        $tempToken = $getTempToken['config_value'];
                        $objGlobalPassword = getSingleTableData($connection, TABLE_ADMIN_CONFIG, "", "config_value", "", array('config_key' => 'globalPassword', 'is_delete' => DELETE_STATUS::NOT_DELETE));
                        if (!empty($objGlobalPassword)) {
                            $masterKey = $objGlobalPassword['config_value'];
                            $security = new Security();
                            if ($accessvalue == "nousername") {
                                // check user passed temporary token or request with temporary token.

                                if ($secretvalue == NULL) {
//                                    echo "\n temp=>".$tempToken;
//                                    echo "\n master=>".$masterKey;
//                                    echo "\n new serc==> ".
                                    $secretvalue = $security->encrypt($tempToken, $masterKey);
                                    $response = array();
                                    $response['key'] = "Temp";// return temporary token
                                    $response['value'] = $secretvalue;
                                    return $response;
                                } else {
                                    $secretvalue = $security->decrypt($secretvalue, $masterKey);
                                    // match token is valid or not
                                    if ($secretvalue == $tempToken) {
                                        return YES;
                                    } else {
                                        return NO;
                                    }
                                }
                            }
                            else {
//                                                        echo "acces=>".$accessvalue;
//                                                        echo "\nsec=>".$secretvalue;
                                $tempToken = $security->encrypt($tempToken, $masterKey);
                                return $this->checkCredentialsForSecurityNew($accessvalue, $secretvalue, $tempToken);
                            }
                        }
                    }
                }
            }
        }
    return NO;
    }

    // USED METHODS
    public function getAdminConfigWithToken($postData)
    {
        $data = array();
        $connection = $this->connection;
        $secret_key = validateObject($postData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $access_key = validateObject($postData, 'access_key', "");
        $access_key = addslashes($access_key);
        if ($access_key == "") {
            $data[STATUS_KEY] = FAILED;
            $data[MESSAGE_KEY] = TOKEN_ERROR;
        }
        else {
            $isSecure = $this->checkForSecurityNew($access_key, $secret_key);
            if ($isSecure != NO) {
                $stmt_get_admin_config=getMultipleTableData($connection,TABLE_ADMIN_CONFIG,"","config_key,config_value"," config_key IN('globalPassword','userAgent','tempToken')",array('is_delete'=>DELETE_STATUS::NOT_DELETE ));
                if($stmt_get_admin_config->rowCount() > 0){
                    while($objAdminConfig=$stmt_get_admin_config->fetch(PDO::FETCH_ASSOC)){
                        $data[$objAdminConfig['config_key']] = $objAdminConfig['config_value'];
                    }
                }
                $stmt_get_admin_config->closeCursor();
            } else {
                $data = "";
            }
        }
        return $data;
    }
}
