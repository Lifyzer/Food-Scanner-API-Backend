<?php
/**
 * Created by PhpStorm.
 * User: c119
 * Date: 03/03/15
 * Time: 5:10 PM
 */

// print array with format
function pr($arr = null, $exit = 1, $append_text = null) {
    if ($arr != null) {
        echo "<pre>";
        if ($arr != null)
            echo $append_text;

        print_r($arr);

        if ($exit == 1)
            exit;
    }
}


 function validateValue($value, $placeHolder) {
    $value = strlen($value) > 0 ? $value : $placeHolder;
    return $value;
}

function validateObject($object, $key, $placeHolder) {

    if(isset($object -> $key))
    {
//        $value = validateValue($object->$key, "");
        return $object->$key;
    }
    else
    {
        return $placeHolder;
    }
}

function json_validate($string) {
    if (is_string($string)) {
        @json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
    return false;
}

function getDefaultDate()
{
    return date("Y-m-d H:i:s");
}

function generatePassword($password)
{
    $cost = 10;

    $saltPassword = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
    $saltPassword = sprintf("$2a$%02d$", $cost). $saltPassword;

    $finalHashPassword = crypt($password, $saltPassword);

    return $finalHashPassword;
}

function matchPassword($userPassword, $dbPassword)
{
    if (crypt($userPassword, $dbPassword) == $dbPassword)
        return 1;
    else
        return 0;
}

function matchStringValue($str1, $str2)
{
    if (strcmp($str1, $str2))
        return 1;
    else
        return 0;
}

function encryptPassword( $str ) {
//    $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( ENCRYPTION_KEY ), $str, MCRYPT_MODE_CBC, md5( md5( ENCRYPTION_KEY ) ) ) );

    $qEncoded      = md5($str );

    return( $qEncoded );
}

function decryptPassword( $str ) {
    $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( ENCRYPTION_KEY ), base64_decode( $str ), MCRYPT_MODE_CBC, md5( md5( ENCRYPTION_KEY ) ) ), "\0");
    return( $qDecoded );
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function generatecoupontring($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}


function generateRandomCode($length) 
{
	    $numbers = range('0','9');
	    $final_array = array_merge($numbers);
        while($length--) 
	       {
    		  $key = array_rand($final_array);
		      $randomString = $final_array[$key];
           }
	     return $randomString;
}

function validateFormdata($object, $key, $placeHolder)
{

    if (isset($object[$key])) {
//        $value = validateValue($object->$key, "");
        return $object[$key];
    } else {
        return $placeHolder;
    }
}

function addTagsToFeed($tags, $isTest, &$isQuerySuccess, mysqli $con)
{
    $currentTags = array();

    foreach ($tags as $hashTag) {
        if ($isQuerySuccess) {

            $hashTag = strtolower($hashTag);

            $selectQuery = "SELECT id FROM " . TABLE_HASHTAGS . " WHERE tag_name = ?";
            $stmt = $con->prepare($selectQuery);
            $tagId = -1;
            $stmt->bind_param("s", $hashTag);
            $stmt->execute();
            $stmt->bind_result($tagId);
            $stmt->fetch();
            $stmt->close();

            if ($tagId > 0) {

                $currentTags[] = $tagId;

                $updateTag = "UPDATE " . TABLE_HASHTAGS . " SET tag_count=tag_count+1 WHERE id=?";
                $stmtUpdate = $con->prepare($updateTag);
                $stmtUpdate->bind_param("i", $tagId);
                if ($stmtUpdate->execute()) {
                    $stmtUpdate->close();
                    $isQuerySuccess = true;
                } else {
                    $isQuerySuccess = false;
                    break;
                }
            } else {
                $tagCount = 1;

                $createdDate = getDefaultDate();
                $insertQuery = "INSERT INTO " . TABLE_HASHTAGS . "(tag_name,tag_count,is_test,created_date) VALUES(?,?,?,?)";

                $insertStmt = $con->prepare($insertQuery);
                $insertStmt->bind_param('siss', $hashTag, $tagCount, $isTest, $createdDate);

                if ($insertStmt->execute()) {
                    $insertStmt->close();
                    $currentTags[] = mysqli_insert_id($con);
                    $isQuerySuccess = true;
                } else {
                    $isQuerySuccess = false;
                    break;
                }
            }
        }
    }
    return $currentTags;
}

function copy_value($v) 
{
    return $v;
}
function fetch_assoc_all_values($stmt)
{
    if($stmt->num_rows>0)
    {
        $result = array();
        $md = $stmt->result_metadata();
        $params = array();
        while($field = $md->fetch_field()) {
            $params[] = &$result[$field->name];
        }
        call_user_func_array(array($stmt, 'bind_result'), $params);
        if($stmt->fetch())
            return $result;
    }

    return null;
}


function fetch_assoc_stmt(\mysqli_stmt $stmt, $buffer = true) 
{
    if ($buffer) 
    {
        $stmt->store_result();
    }
    $fields = $stmt->result_metadata()->fetch_fields();
    $args = array();
    foreach($fields AS $field) 
    {
        $key = str_replace(' ', '_', $field->name); // space may be valid SQL, but not PHP
        $args[$key] = &$field->name; // this way the array key is also preserved
    }
    call_user_func_array(array($stmt, "bind_result"), $args);

    $results = array();
    while($stmt->fetch()) 
    {
        //$results[] = array_map(array($this, "copy_value"), $args);        
        $results[] = array_map("copy_value", $args); 
    }
    if ($buffer) 
    {
        $stmt->free_result();
    }
    return $results;
}

function fetch_stmt_with_attributes($stmt)
{
    if($stmt->num_rows>0)
    {
        $result = array();
        $md = $stmt->result_metadata();
        $params = array();
        while($field = $md->fetch_field()) {
            $params[] = &$result[$field->name];
        }
        call_user_func_array(array($stmt, 'bind_result'), $params);
        if($stmt->fetch())
            return $result;
    }

    return null;
}



?>