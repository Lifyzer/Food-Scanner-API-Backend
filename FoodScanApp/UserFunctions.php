<?php
/**
 * Created by PhpStorm.
 * User: c157
 * Date: 17/01/18
 * Time: 11:53 AM
 */

//include_once 'SendEmail.php';
include_once 'TableVars.php';
include_once 'ConstantValues.php';

class UserFunctions
{
    function __construct()
    {

    }

    public function call_service($service, $postData)
    {
        switch ($service) {
            case "Login": {
                return $this->Login($postData);
            }
                break;

            case "Registration": {
                return $this->Registration($postData);
            }
                break;

            case "ChangePassword": {
                return $this->ChangePassword($postData);
            }
                break;
            case "EditProfile": {
                return $this->EditProfile($postData);
            }
                break;
        }
    }


    public function Registration($userData)
    {
        $connection = $GLOBALS['con'];

        $email_id = validateObject($userData, 'email_id', "");
        $email_id = addslashes($email_id);

        $password = validateObject($userData, 'password', "");
        $password = addslashes($password);
        $password = encryptPassword($password);

        $first_name = validateObject($userData, 'first_name', "");
        $first_name = addslashes($first_name);

        $last_name = validateObject($userData, 'last_name', "");
        $last_name = addslashes($last_name);

        $device_type = validateObject($userData, 'device_type', "");
        $device_type = addslashes($device_type);

        $accsee_key = validateObject($userData, 'access_key', "");
        $accsee_key = addslashes($accsee_key);

        $secret_key = validateObject($userData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $posts = array();
        $token = "";

        $is_delete = IS_DELETE;
        $created_date = date("Y-m-d H:i:s");


        $select_email_query = "Select * from " . TABLE_USER . "  where 	email = ? and is_delete = ?";


        $select_email_stmt = $connection->prepare($select_email_query);
        $select_email_stmt->bind_param("ss", $email_id, $is_delete);
        $select_email_stmt->execute();
        $select_email_stmt->store_result();

        if ($select_email_stmt->num_rows > 0) {

            $select_email_stmt->close();
            $status = 2;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = EMAIL_ALREADY_EXISTS;
            $data['User'] = $posts;

            return $data;

        }

        //****  INSERT USER ****//

        $security = new SecurityFunctions($connection);
        $generate_guid = $security->gen_uuid();

        $insertFields = "email,
                        first_name,
                        last_name,
                        password,
                        device_type,
                        created_date,
                        guid
                        ";

        $insert_user_query = "Insert into " . TABLE_USER . " (" . $insertFields . ") values(?,?,?,?,?,?,?)";

//        echo $insert_user_query;
//
//        echo "Email : ".$email_id . " First naem : " .$first_name . " Last Name : " .$last_name . " PAss : " .$password. " Device type : ".$device_type ." cReated date : " . $created_date ." Uid :" .$generate_guid ;

        $insert_user_stmt = $connection->prepare($insert_user_query);
        //$insert_user_stmt->bind_param("sssssss", $email_id, $first_name, $last_name, $password, $device_type, $created_date, $accsee_key);
        $insert_user_stmt->bind_param("sssssss", $email_id, $first_name, $last_name, $password, $device_type, $created_date, $generate_guid);



        if ($insert_user_stmt->execute()) {

            $user_inserted_id = mysqli_insert_id($connection);

            $select_user = "select u.* from " . TABLE_USER . " as u where u.`id` = ? and u.is_delete = ?";
            $select_user_stmt = $connection->prepare($select_user);
            $select_user_stmt->bind_param("ss", $user_inserted_id, $is_delete);

            if ($select_user_stmt->execute()) {


                $select_user_stmt->store_result();
                if ($select_user_stmt->num_rows > 0) {

                    while ($user = fetch_assoc_all_values($select_user_stmt)) {

                        //Update Token for user
                        $tokenData = new stdClass;
                        $tokenData->GUID = $user['guid'];
                        $tokenData->userId = $user['id'];
                        $user_token = $security->updateTokenforUser($tokenData);

                        if ($user_token[STATUS_KEY] == "success") {
                            $token = $user_token['UserToken'];
                        }


                        $posts[] = $user;
                    }
                }

                $select_user_stmt->close();
                $status = 1;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = REGISTRATION_SUCCESSFULLY_DONE;
                $data['UserToken'] = $token;
                $data['User'] = $posts;

                return $data;

            } else {

                $select_user_stmt->close();
                $status = 2;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                $data['User'] = $posts;
                return $data;

            }

        } else {

            $insert_user_stmt->close();
            $status = 2;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            $data['Parent'] = $posts;
            return $data;

        }
    }


    public function EditProfile($userData)
    {
        $connection = $GLOBALS['con'];

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $email_id = validateObject($userData, 'email_id', "");
        $email_id = addslashes($email_id);

        $first_name = validateObject($userData, 'first_name', "");
        $first_name = addslashes($first_name);

        $is_delete = IS_DELETE;
        $posts = array();

    }

    public function ChangePassword($userData)
    {

        $connection = $GLOBALS['con'];

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $password = validateObject($userData, 'password', "");
        $password = addslashes($password);
        $password = encryptPassword($password);

        $new_password = validateObject($userData, 'new_password', "");
        $new_password = addslashes($new_password);
        $new_password = encryptPassword($new_password);

        $is_delete = IS_DELETE;
        $posts = array();

        $select_user_query = "Select * from " . TABLE_USER . "  where id = ? and is_delete = ?";
        $select_user_stmt = $connection->prepare($select_user_query);
        $select_user_stmt->bind_param("ss", $user_id, $is_delete);

        if ($select_user_stmt->execute()) {
            $select_user_stmt->store_result();
            if ($select_user_stmt->num_rows > 0) {

                while ($user = fetch_assoc_all_values($select_user_stmt)) {

                    if ($password === $user['password']) {

                        $update_password_query = "update user as u set u.password = ? WHERE u.id = ?";
                        $update_password_stmt = $connection->prepare($update_password_query);
                        $update_password_stmt->bind_param("ss", $new_password, $user_id);

                        if ($update_password_stmt->execute()) {

                            $selected_user = "select u.* from " . TABLE_USER . " as u where u.`id` = ?";
                            $selected_user_stmt = $connection->prepare($selected_user);
                            $selected_user_stmt->bind_param("s", $user_id);
                            if ($selected_user_stmt->execute()) {

                                $selected_user_stmt->store_result();
                                if ($selected_user_stmt->num_rows > 0) {
                                    while ($user = fetch_assoc_all_values($selected_user_stmt)) {
                                        $posts[] = $user;
                                    }
                                }

                                $select_user_stmt->close();
                                $status = 1;
                                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                                $data['message'] = PASSWORD_CHANGED_SUCCESSFULLY;
                                $data['User'] = $posts;

                                return $data;

                            } else {

                                $status = 2;
                                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                                $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                                $data['User'] = $posts;
                                return $data;

                            }
                        } else {

                            $status = 2;
                            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                            $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                            $data['Parent'] = $posts;
                            return $data;

                        }
                    } else {

                        //Password wrong
                        $status = 2;
                        $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                        $data['message'] = WRONG_PASSWORD_MESSAGE;
                        $data['User'] = $posts;

                        return $data;
                    }
                }
            } else {

                $select_user_stmt->close();
                $status = 2;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = NO_DATA_AVAILABLE;
                $data['User'] = $posts;
                return $data;

            }
        } else {

            $select_user_stmt->close();
            $status = 2;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            $data['Parent'] = $posts;
            return $data;

        }

    }

    public function Login($userData)
    {
        $connection = $GLOBALS['con'];

        $email_id = validateObject($userData, 'email_id', "");
        $email_id = addslashes($email_id);

        $password = validateObject($userData, 'password', "");
        $password = addslashes($password);

        $device_type = validateObject($userData, 'device_type', "");
        $device_type = addslashes($device_type);

        $accsee_key = validateObject($userData, 'access_key', "");
        $accsee_key = addslashes($accsee_key);

        $secret_key = validateObject($userData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $posts = array();

        $is_delete = IS_DELETE;

        $token = "";

        $select_user_query = "Select * from " . TABLE_USER . "  where email = ? and is_delete = ?";

        $select_user_stmt = $connection->prepare($select_user_query);
        $select_user_stmt->bind_param("ss", $email_id, $is_delete);

        if ($select_user_stmt->execute()) {
            $select_user_stmt->store_result();
            if ($select_user_stmt->num_rows > 0) {

                while ($user = fetch_assoc_all_values($select_user_stmt)) {

                    if (encryptPassword($password) === $user['password']) {

                        $user_id = $user['id'];

                        $update_user_query = "Update " . TABLE_USER . " set  device_type = ? where  id = ?";

                        $update_user_stmt = $connection->prepare($update_user_query);
                        $update_user_stmt->bind_param("ss", $device_type, $user_id);

                        $update_user_stmt->execute();

//                        if ($update_user_stmt->execute()){
//
//                        }

                        //Update Token for user
                        if ($user['guid'] == null || $user['guid'] == "") {
                            $generate_user_guid = $this->updateGuidForUser($user_id);
                        } else {
                            $generate_user_guid = $user['guid'];
                        }


                        $tokenData = new stdClass;
                        $tokenData->GUID = $generate_user_guid;
                        $tokenData->userId = $user_id;

                        $security = new SecurityFunctions($connection);
                        $user_token = $security->updateTokenforUser($tokenData);

                        if ($user_token[STATUS_KEY] == "success") {
                            $token = $user_token['UserToken'];
                        }

                        $posts[] = $user;

                        $select_user_stmt->close();
                        $status = 1;
                        $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                        $data['message'] = USER_LOGIN_SUCCESSFULLY;
                        $data['UserToken'] = $token;
                        $data['User'] = $posts;

                        return $data;


                    } else {

                        $status = 2;
                        $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                        $data['message'] = WRONG_PASSWORD_MESSAGE;
                        $data['User'] = $posts;

                        return $data;

                    }
                }

            } else {

                $select_user_stmt->close();
                $status = 2;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = NO_DATA_AVAILABLE;
                $data['User'] = $posts;
                return $data;

            }

        } else {

            $select_user_stmt->close();
            $status = 2;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            $data['Parent'] = $posts;
            return $data;

        }

    }


    function updateGuidForUser($user_id)
    {
        $connection = $GLOBALS['con'];

        $data = array();
        $selectQuery = "SELECT * FROM " . TABLE_USER . "  WHERE id = ? AND is_delete='0'";
        if ($select_review_stmt = $connection->prepare($selectQuery)) {
            $select_review_stmt->bind_param("s", $user_id);
            $select_review_stmt->execute();
            $select_review_stmt->store_result();
            if ($select_review_stmt->num_rows > 0) {
                $security = new SecurityFunctions($connection);

                $generate_guid = $security->gen_uuid();

                $updatQuery = "UPDATE " . TABLE_USER . " SET guid = ? WHERE id = ? AND is_delete='0'";
                if ($update_stmt = $connection->prepare($updatQuery)) {

                    $update_stmt->bind_param('ss', $generate_guid, $user_id);
                    if ($update_stmt->execute()) {
                        $update_stmt->close();

                        $status = SUCCESS;
                        return $generate_guid;
                    }
                }
            } else {

            }

            $select_review_stmt->close();
        }


        return $data;
    }


}

?>
