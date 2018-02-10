<?php
/**
 * Created by PhpStorm.
 * User: c157
 * Date: 18/01/18
 * Time: 10:56 AM
 */

include_once 'TableVars.php';
include_once 'ConstantValues.php';

class ProductFunctions
{
    function __construct()
    {

    }

    public function call_service($service, $postData)
    {
        switch ($service) {

            case "addToFavourite": {
                return $this->addToFavourite($postData);
            }
                break;

            case "getAllUserFavourite": {
                return $this->getAllUserFavourite($postData);
            }
                break;

            case "getProductDetails": {
                return $this->getProductDetails($postData);
            }
                break;

            case "getUserHistory": {
                return $this->getUserHistory($postData);
            }
                break;

            case "removeProductFromHistory": {
                return $this->removeProductFromHistory($postData);
            }
                break;
        }
    }

    public function getProductDetails($userData)
    {

        $connection = $GLOBALS['con'];

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $product_name = validateObject($userData, 'product_name', "");
        $product_name = addslashes($product_name);

        $accsee_key = validateObject($userData, 'access_key', "");
        $accsee_key = addslashes($accsee_key);

        $secret_key = validateObject($userData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $posts = array();

        $is_delete = IS_DELETE;
        $currentdate = date("Y-m-d H:i:s");

        $select_product_details_query = "SELECT p.* FROM `product` as p
                                         WHERE LOWER(p.product_name) = LOWER(?) AND is_delete = ?
                                         ORDER BY p.created_date LIMIT 1";

        $select_product_details_stmt = $connection->prepare($select_product_details_query);
        $select_product_details_stmt->bind_param("ss", $product_name, $is_delete);
        if ($select_product_details_stmt->execute()) {

            $select_product_details_stmt->store_result();
            if ($select_product_details_stmt->num_rows > 0) {

                //**** Product found in database insert data into history table ****//

                while ($product = fetch_assoc_all_values($select_product_details_stmt)) {

                    $product_id = $product['id'];

                    $select_history_query = "select id from " . TABLE_HISTORY . " where product_id = ? and user_id = ? and is_delete = ?";
                    $select_history_stmt = $connection->prepare($select_history_query);

                    $select_history_stmt->bind_param("sss", $product_id, $user_id, $is_delete);

                    if ($select_history_stmt->execute()) {

                        $select_history_stmt->store_result();
                        if ($select_history_stmt->num_rows > 0) {

                            while ($history = fetch_assoc_all_values($select_history_stmt)) {

                                $history_id = $history['id'];

                                //******** Update history ********//

                                $update_history_query = "Update " . TABLE_HISTORY . " set  created_date = ? where  id = ?";
                                $update_history_stmt = $connection->prepare($update_history_query);
                                $update_history_stmt->bind_param("ss", $currentdate, $history_id);
                                if ($update_history_stmt->execute()) {

                                   // echo "updated id :" .$history_id;

                                    $posts[] = $product;

                                } else {

                                    $status = 2;
                                    $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                                    $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                                    return $data;

                                }

                            }

                        } else {

                            //******** Insert history ********//

                            $insertFields = "user_id,
                                 product_id,
                                 created_date
                                 ";

                            $valuesFields = "?,?,?";
                            $insert_history_query = "Insert into " . TABLE_HISTORY . " (" . $insertFields . ") values(" . $valuesFields . ")";
                            $insert_history_stmt = $connection->prepare($insert_history_query);

                            $insert_history_stmt->bind_param("sss", $user_id, $product_id, $currentdate);

//                            echo $insert_history_query;
//                            echo "User ID : " . $user_id . " Product Id " . $product_id . " created_date " . $currentdate;

                            if ($insert_history_stmt->execute()) {

                                $posts[] = $product;

                            } else {

                                $status = 2;
                                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                                $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                                return $data;

                            }
                        }
                    }
                }

                $select_history_stmt->close();
                $status = 1;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = DATA_FETCHED_SUCCESSFULLY;
                $data['product'] = $posts;
                return $data;


            } else {

                //**** No product found in database ****//

                $status = 1;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = NO_PRODUCT_FOUND_IN_DATABASE;
                $data['product'] = $posts;
                return $data;

            }


        } else {

            $status = 2;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            return $data;

        }
    }

    public function removeProductFromHistory($userData)
    {

        $connection = $GLOBALS['con'];

        $history_id = validateObject($userData, 'history_id', "");
        $history_id = addslashes($history_id);

        $accsee_key = validateObject($userData, 'access_key', "");
        $accsee_key = addslashes($accsee_key);

        $secret_key = validateObject($userData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $posts = array();
        $is_delete = '1';

        $delete_user_history_query = "UPDATE " . TABLE_HISTORY . " as h set h.is_delete = ? WHERE h.id = ?";

        $delete_user_history_stmt = $connection->prepare($delete_user_history_query);
        $delete_user_history_stmt->bind_param("ss", $is_delete, $history_id);
        if ($delete_user_history_stmt->execute()) {

            $select_history = "select h.* from " . TABLE_HISTORY . " as h where h.id = ?";
            $select_history_stmt = $connection->prepare($select_history);
            $select_history_stmt->bind_param("s", $history_id);
            if($select_history_stmt->execute()){

                $select_history_stmt->store_result();
                if ($select_history_stmt->num_rows > 0){

                    while ($history = fetch_assoc_all_values($select_history_stmt)){

                        $update_fav_query = "Update " .TABLE_FAVOURITE. " as f set f.is_favourite = '0' where f.user_id = ? and f.product_id = ? and f.is_delete = ? ";
                        $update_fav_stmt = $connection->prepare($update_fav_query);
                        $update_fav_stmt->bind_param("sss", $history['user_id'] , $history['product_id'] , $is_delete);

                        if($update_fav_stmt->execute()){
                            //echo "updated";
                        }

                    }


                }

            }


            $status = 1;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = HISTORY_REMOVED_SUCCESSFULLY;
            return $data;

        } else {

            $status = 2;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            return $data;

        }
    }


    public function getUserHistory($userData)
    {

        $connection = $GLOBALS['con'];

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $to_index = validateObject($userData, 'to_index', "");
        $to_index = addslashes($to_index);

        $from_index = validateObject($userData, 'from_index', "");
        $from_index = addslashes($from_index);

        $accsee_key = validateObject($userData, 'access_key', "");
        $accsee_key = addslashes($accsee_key);

        $secret_key = validateObject($userData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $posts = array();
        $is_delete = IS_DELETE;

        $select_user_history_query = "select h.id as history_id, h.user_id, h.product_id, h.created_date as history_created_date , p.* from history as h
                                      left JOIN product as p on p.id = h.product_id
                                      WHERE h.user_id = ? and h.is_delete = ? ORDER BY h.created_date DESC limit ?,? ";

        $select_user_history_stmt = $connection->prepare($select_user_history_query);
        $select_user_history_stmt->bind_param("ssss", $user_id, $is_delete, $from_index, $to_index);
        if ($select_user_history_stmt->execute()) {

            $select_user_history_stmt->store_result();
            if ($select_user_history_stmt->num_rows > 0) {

                while ($history = fetch_assoc_all_values($select_user_history_stmt)) {

                    $is_favourite = 1;

                    $select_fav_query = "select f.id from favourite as f where f.user_id = ? and f.product_id = ? and f.is_favourite = ? and f.is_delete = ?";
                    $select_fav_stmt = $connection->prepare($select_fav_query);
                    $select_fav_stmt->bind_param("ssss", $user_id, $history['product_id'], $is_favourite, $is_delete);
                    if ($select_fav_stmt->execute()) {

                        $select_fav_stmt->store_result();
                        if ($select_fav_stmt->num_rows > 0) {
                            $history['is_favourite'] = 1;
                        } else {
                            $history['is_favourite'] = 0;
                        }
                    }

                    $posts[] = $history;
                }

                $select_user_history_stmt->close();
                $status = 1;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = DATA_FETCHED_SUCCESSFULLY;
                $data['history'] = $posts;
                return $data;

            } else {

                $status = 1;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = NO_FAVOURITE_HISTORY_FOUND;
                $data['history'] = $posts;
                return $data;

            }

        } else {

            $status = 2;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            return $data;

        }
    }


    public function getAllUserFavourite($userData)
    {

        $connection = $GLOBALS['con'];

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $accsee_key = validateObject($userData, 'access_key', "");
        $accsee_key = addslashes($accsee_key);

        $secret_key = validateObject($userData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $posts = array();

        $is_delete = IS_DELETE;
        $is_favourite = "1";

        $select_user_favourite_query = "SELECT f.id as favourite_id , f.user_id, f.product_id, f.is_favourite , p.*FROM " . TABLE_FAVOURITE . " as f
                                        left join " . TABLE_PRODUCT . " as p on p.id = f.product_id
                                        where f.user_id = ? and f.is_favourite = ? and f.is_delete = ? ORDER BY f.created_date DESC ";

        $select_user_favourite_stmt = $connection->prepare($select_user_favourite_query);
        $select_user_favourite_stmt->bind_param("sss", $user_id, $is_favourite, $is_delete);
        if ($select_user_favourite_stmt->execute()) {

            $select_user_favourite_stmt->store_result();
            if ($select_user_favourite_stmt->num_rows > 0) {

                while ($product = fetch_assoc_all_values($select_user_favourite_stmt)) {
                    $posts[] = $product;
                }

                $select_user_favourite_stmt->close();
                $status = 1;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = DATA_FETCHED_SUCCESSFULLY;
                $data['product'] = $posts;
                return $data;

            } else {

                $status = 1;
                $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                $data['message'] = NO_FAVOURITE_PRODUCT_FOUND;
                $data['product'] = $posts;
                return $data;

            }


        } else {

            $status = 2;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            return $data;

        }
    }

    public function addToFavourite($userData)
    {

        $connection = $GLOBALS['con'];

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $product_id = validateObject($userData, 'product_id', "");
        $product_id = addslashes($product_id);

        $is_favourite = validateObject($userData, 'is_favourite', "");
        $is_favourite = addslashes($product_id);

        $accsee_key = validateObject($userData, 'access_key', "");
        $accsee_key = addslashes($accsee_key);

        $secret_key = validateObject($userData, 'secret_key', "");
        $secret_key = addslashes($secret_key);

        $posts = array();

        $is_delete = IS_DELETE;
        $currentdate = date("Y-m-d H:i:s");

        $select_favourite_query = "select id,is_favourite from " . TABLE_FAVOURITE . " where product_id = ? and user_id = ? and is_delete = ?";
        $select_favourite_stmt = $connection->prepare($select_favourite_query);

        $select_favourite_stmt->bind_param("sss", $product_id, $user_id, $is_delete);

        if ($select_favourite_stmt->execute()) {

            $select_favourite_stmt->store_result();
            if ($select_favourite_stmt->num_rows > 0) {

                //**** "Update" ****//

                $favourite_product = fetch_assoc_all_values($select_favourite_stmt);

                $update_favourite_query = "update " . TABLE_FAVOURITE . " set is_favourite = ? where id = ?";
                $update_favourite_stmt = $connection->prepare($update_favourite_query);
                $update_favourite_stmt->bind_param("ss", $is_favourite, $favourite_product["id"]);
                if ($update_favourite_stmt->execute()) {

                    $status = 1;
                    $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                    $data['message'] = "Like / Dislike updated Successfully !!!";
                    return $data;

                } else {

                    $status = 2;
                    $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                    $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                    return $data;

                }
            } else {

                //**** "Insert" ****//

                $insertFields = "user_id,
                                 product_id,
                                 is_favourite,
                                 created_date
                                 ";

                $valuesFields = "?,?,?,?";
                $insert_favourite_query = "Insert into " . TABLE_FAVOURITE . " (" . $insertFields . ") values(" . $valuesFields . ")";
                $insert_favourite_stmt = $connection->prepare($insert_favourite_query);

                $insert_favourite_stmt->bind_param("ssss", $user_id, $product_id, $is_favourite, $currentdate);
                if ($insert_favourite_stmt->execute()) {

                    $status = 1;
                    $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                    $data['message'] = FAVOURITE_SUCCESSFULLY;
                    return $data;

                } else {

                    $status = 2;
                    $data['status'] = ($status > 1) ? FAILED : SUCCESS;
                    $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                    return $data;

                }

            }
        } else {

            $status = 2;
            $data['status'] = ($status > 1) ? FAILED : SUCCESS;
            $data['message'] = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            return $data;

        }

    }


}

?>
