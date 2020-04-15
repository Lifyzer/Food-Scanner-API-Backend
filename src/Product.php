<?php

namespace Lifyzer\Api;

use PDO;
use phpFastCache\Helper\Psr16Adapter;

class Product
{
    private const CACHE_DRIVER = 'Files';
    protected $connection;

    public function __construct(PDO $con)
    {
        $this->connection = $con;
    }

    public function callService($service, $postData)
    {
        switch ($service) {
            case 'updateRatingStatus':
                return $this->updateRatingStatus($postData);

            case 'addToFavourite':
                return $this->addToFavourite($postData);

            case 'getAllUserFavourite':
                return $this->getAllUserFavourite($postData);

            case 'getProductDetails':
                return $this->getProductDetails($postData);

            case 'getUserHistory':
                return $this->getUserHistory($postData);

            case 'removeProductFromHistory':
                return $this->removeProductFromHistory($postData);

            case 'addReview':
                return $this->addReview($postData);

            case 'getReviewList':
                return $this->getReviewList($postData);

            case 'updateReview':
                return $this->updateReview($postData);

            case 'deleteReview':
                return $this->deleteReview($postData);

            default:
                return null;
        }
    }

    public function updateRatingStatus($userData)
    {
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $device_type = validateObject($userData, 'device_type', "");
        $device_type = addslashes($device_type);

        $rate_status = '1';

        $select_user_rated_query = "SELECT * from ".TABLE_RATING." WHERE user_id = ".$user_id." 
                                    AND is_rate = '1'
                                    AND device_type = '".$device_type."'
                                    AND is_test = '".IS_TESTDATA."' AND is_delete = '".IS_DELETE."' ";
        $select_user_rated_stmt = getSingleTableData(
            $connection,
            "",
            $select_user_rated_query,
            "", "", []);
        if (empty($select_user_rated_stmt)){
            $conditional_array = ['is_rate' => $rate_status, 'user_id' => $user_id, 'device_type' => $device_type, 'is_test' => IS_TESTDATA ];
            $rating_response = addData(
                $connection,
                "updateRatingStatus",
                TABLE_RATING,
                $conditional_array);

            if ($rating_response[STATUS_KEY] == SUCCESS) {
                $message = RATING_STATUS_STORED_SUCCESSFULLY;
                $status = SUCCESS;
            } else {
                $status = FAILED;
                $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            }
        }else{
            $message = RATING_STATUS_STORED_SUCCESSFULLY;
            $status = SUCCESS;
        }
        $data['status'] = $status;
        $data['message'] = $message;

        return $data;
    }

    public function deleteReview($userData)
    {
        $connection = $this->connection;

        $review_id = validateObject($userData, 'review_id', "");
        $review_id = addslashes($review_id);

        $edit_history_response = editData(
            $connection,
            "deleteReview",
            TABLE_REVIEW,
            ['is_delete' => DELETE_STATUS::IS_DELETE],
            ['id' => $review_id,'is_test' =>IS_TESTDATA],
            "");
        if ($edit_history_response[STATUS_KEY] === SUCCESS) {
            $message = REVIEW_REMOVED_SUCCESSFULLY;
            $status = SUCCESS;
        } else {
            $status = FAILED;
            $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
        }
        $data['status'] = $status;
        $data['message'] = $message;
        return $data;
    }

    public function updateReview($userData)
    {
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $review_id = validateObject($userData, 'review_id', "");
        $review_id = addslashes($review_id);

        $ratting = validateObject($userData, 'ratting', '');
        $ratting = addslashes($ratting);

        $desc = validateObject($userData, 'desc', '');
        $desc = utf8_decode($desc);

        $is_rate = false;

        $edit_review_response = editData(
            $connection,
            "updateReview",
            TABLE_REVIEW,
            ['ratting'=>$ratting,'description'=>$desc],
            ['id' => $review_id,'is_test' =>IS_TESTDATA,'is_delete' => IS_DELETE],
            "");

        if ($edit_review_response[STATUS_KEY] === SUCCESS) {
                $message = REVIEW_UPDATED_SUCCESSFULLY;
                $status = SUCCESS;
                //START : if user have left 2 comments or rated 3 products (or more) then will rate status as TRUE else FALSE.
                $rate_status = $this->IsUserRate($user_id);
                if($rate_status == SUCCESS){
                    $select_user_rate_query = "(SELECT COUNT(*) from ".TABLE_REVIEW." WHERE user_id = ".$user_id." AND is_test = '".IS_TESTDATA."' AND is_delete = '".IS_DELETE."') as count_rate";
                    $select_user_desc_query = "(SELECT COUNT(*) from ".TABLE_REVIEW." WHERE user_id = ".$user_id." AND is_test = '".IS_TESTDATA."' AND is_delete = '".IS_DELETE."' AND description != '') as count_comment";
                    $select_user_review_query = "SELECT ".$select_user_rate_query.",".$select_user_desc_query;
                    $select_user_review_stmt = getSingleTableData(
                        $connection,
                        "",
                        $select_user_review_query,
                        "", "", []);
                    if (!empty($select_user_review_stmt) &&
                        ($select_user_review_stmt['count_comment'] >= 2 || $select_user_review_stmt["count_rate"] >= 3)) {
                        $is_rate = true;
                    }else{
                        $is_rate = false;
                    }
                }else {
                    $is_rate = false;
                }
                //END
        } else {
            $status = FAILED;
            $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
        }
        $data['is_rate'] = $is_rate;
        $data['status'] = $status;
        $data['message'] = $message;
        return $data;
    }

    public function addReview($userData)
    {
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', '');
        $user_id = addslashes($user_id);

        $product_id = validateObject($userData, 'product_id', '');
        $product_id = addslashes($product_id);

        $ratting = validateObject($userData, 'ratting', '');
        $ratting = addslashes($ratting);

        $desc = validateObject($userData, 'desc', '');
        $desc = utf8_decode($desc);

        $is_rate = false;

        editData(
            $connection,
            "updateReview",
            TABLE_REVIEW,
            ['is_delete' => DELETE_STATUS::IS_DELETE],
            ['user_id' => $user_id,'product_id' => $product_id,'is_test' =>IS_TESTDATA,'is_delete' => IS_DELETE],
            "");

        $conditional_array = ['user_id' => $user_id, 'product_id' => $product_id, 'ratting' => $ratting, 'description' => $desc ,'is_test' => IS_TESTDATA ];
        $favourite_response = addData(
            $connection, "addReview",
            TABLE_REVIEW,
            $conditional_array);

        if ($favourite_response[STATUS_KEY] == SUCCESS) {
            $status = SUCCESS;
            $message = REVIEW_ADDED_SUCCESSFULLY;
            //START : if user have left 2 comments or rated 3 products (or more) then will rate status as TRUE else FALSE.
            $rate_status = $this->IsUserRate($user_id);
            if($rate_status == SUCCESS){
                $select_user_rate_query = "(SELECT COUNT(*) from ".TABLE_REVIEW." WHERE user_id = ".$user_id." AND is_test = '".IS_TESTDATA."' AND is_delete = '".IS_DELETE."') as count_rate";
                $select_user_desc_query = "(SELECT COUNT(*) from ".TABLE_REVIEW." WHERE user_id = ".$user_id." AND is_test = '".IS_TESTDATA."' AND is_delete = '".IS_DELETE."' AND description != '') as count_comment";
                $select_user_review_query = "SELECT ".$select_user_rate_query.",".$select_user_desc_query;
                $select_user_review_stmt = getSingleTableData($connection, "", $select_user_review_query, "", "", []);
                if (!empty($select_user_review_stmt) &&
                    ($select_user_review_stmt['count_comment'] >= 2 || $select_user_review_stmt["count_rate"] >= 3)) {
                    $is_rate = true;
                }else{
                    $is_rate = false;
                }
            }else {
                $is_rate = false;
            }
            //END
        } else {
            $status = FAILED;
            $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
        }
        $data['is_rate'] = $is_rate;
        $data['status'] = $status;
        $data['message'] = $message;
        return $data;
    }

    public function getReviewList($userData)
    {
        $connection = $this->connection;
        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $product_id = validateObject($userData, 'product_id', '');
        $product_id = addslashes($product_id);

        $to_index = validateObject($userData, 'to_index', "");
        $to_index = addslashes($to_index);

        $from_index = validateObject($userData, 'from_index', "");
        $from_index = addslashes($from_index);

        $posts = [];

        $select_total_review_query = "Select count(*) as total_review, avg(ratting) as avg_review , sum(ratting) as total_ratting from ". TABLE_REVIEW ." r where product_id = '".$product_id."' and is_test = '".IS_TESTDATA."' and is_delete = '".IS_DELETE."'";
        $select_total_review_stmt = getSingleTableData(
            $connection, "",
            $select_total_review_query,
            "","","");
        if (!empty($select_total_review_stmt)) {
            $posts['total_review'] = $select_total_review_stmt['total_review'];
            $posts['avg_review'] = $select_total_review_stmt['avg_review'];
            $posts['total_ratting'] = $select_total_review_stmt['total_ratting'];
        }

        $select_total_cust_review_query = "Select count(*) as total_cust_review, avg(ratting) as avg_cust_review from ". TABLE_REVIEW ." r where user_id != '".$user_id."' and product_id = '".$product_id."' and is_test = '".IS_TESTDATA."' and is_delete = '".IS_DELETE."'";
        $select_total_cust_review_stmt = getSingleTableData(
            $connection,"",
            $select_total_cust_review_query,"","","");
        if (!empty($select_total_cust_review_stmt)) {
            $posts['total_cust_review'] = $select_total_cust_review_stmt['total_cust_review'];
            $posts['avg_cust_review'] = $select_total_cust_review_stmt['avg_cust_review'];
        }

        $select_total_user_query = "Select count(*) as total_user from ". TABLE_USER ." u where is_test = '".IS_TESTDATA."' and is_delete = '".IS_DELETE."'";
        $select_total_user_stmt = getSingleTableData(
            $connection,"",
            $select_total_user_query,"","","");
        if (!empty($select_total_user_stmt)) {
                $posts['total_user'] = $select_total_user_stmt['total_user'];
        }

        $select_user_query = "Select r.id,u.first_name,u.last_name,u.user_image,r.description,r.ratting,r.modified_date from ". TABLE_REVIEW ." r,". TABLE_USER ." u,". TABLE_PRODUCT ." p where r.is_test = '".IS_TESTDATA."' and  r.user_id = u.id and r.product_id = p.id and r.user_id = '".$user_id."' and r.product_id = '".$product_id."' and r.is_delete = '".IS_DELETE."'";
        $select_user_stmt = getMultipleTableData(
            $connection, "",
            $select_user_query, "", "");
        if ($select_user_stmt->rowCount() > 0) {
            while ($product = $select_user_stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts['user_review'][] = $product;
            }
        } else {
            $posts['user_review'] = [];
        }

        $select_customer_query = "Select r.id,u.first_name,u.last_name,u.user_image,r.description,r.ratting,r.modified_date from ". TABLE_REVIEW ." r,". TABLE_USER ." u,". TABLE_PRODUCT ." p where r.is_test = '".IS_TESTDATA."' and  r.user_id = u.id and r.product_id = p.id and r.user_id != '".$user_id."' and r.product_id = '".$product_id."' and r.is_delete = '".IS_DELETE."' ORDER BY r.created_date DESC limit $from_index,$to_index ";
        $select_customer_stmt = getMultipleTableData(
            $connection, "",
            $select_customer_query, "", "");
        if ($select_customer_stmt->rowCount() > 0) {
            while ($product = $select_customer_stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts['customer_review'][] = $product;
            }
        } else {
            $posts['customer_review'] = [];
        }

        $data['status'] = SUCCESS;
        $data['message'] = DATA_FETCHED_SUCCESSFULLY;
        $data['data'] = $posts;
        return $data;
    }
    
    public function startsWith($haystack, $needle)
    {
          if(substr($haystack,0, strlen($needle))===$needle){
            return true;
          } else {
            return false;
          }
    }

    public function getProductDetails($userData)
    {
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', '');
        $user_id = addslashes($user_id);

        $product_name = validateObject($userData, 'product_name', '');
        $product_name = utf8_decode($product_name);

        $flag = validateObject($userData, 'flag', 0);
        $flag = addslashes($flag);

        $posts = [];
        $current_date = getDefaultDate();
        $message = "";
        $status = FAILED;
        $is_favourite = 1;

        $select_product_details_stmt = getMultipleTableData(
                $connection,
                TABLE_PRODUCT,
                '',
                '*',
                '(LOWER(product_name) LIKE LOWER(:product_name) OR barcode_id = :barcode) AND is_delete = :is_delete ORDER BY created_date LIMIT 1',
                [
                    'product_name' => $product_name.'%',
                    'barcode' => $product_name,
                    'is_delete' => IS_DELETE
                ]);
        if ($select_product_details_stmt->rowCount() > 0) {
            $status = SUCCESS;
            while ($product = $select_product_details_stmt->fetch(PDO::FETCH_ASSOC)) {

                //get user favourite
                $conditional_array = ['product_id' => $product['id'], 'user_id' => $user_id, 'is_favourite' => $is_favourite, 'is_delete' => IS_DELETE,'is_test' => IS_TESTDATA];
                $objFavourite = getSingleTableData(
                    $connection,
                    TABLE_FAVOURITE, "",
                    "id", "",
                    $conditional_array);
                if (!empty($objFavourite)) {
                    $product['is_favourite'] = 1;
                } else {
                    $product['is_favourite'] = 0;
                }

                //Product found in database insert data into history table
                $product_id = $product['id'];
                $conditional_array = ['product_id' => $product_id, 'user_id' => $user_id, 'is_delete' => IS_DELETE,'is_test' => IS_TESTDATA];
                $objHistory = getSingleTableData(
                    $connection,
                    TABLE_HISTORY, "",
                    "id", "",
                    $conditional_array);
                if (!empty($objHistory)) {
                    $history_id = $objHistory['id'];
                    $edit_history_response = editData(
                        $connection,
                        'getProductDetails',
                        TABLE_HISTORY,
                        ['created_date' => $current_date],
                        ['id' => $history_id,'is_delete' => IS_DELETE,'is_test' => IS_TESTDATA],
                        "");
                    if ($edit_history_response[STATUS_KEY] == SUCCESS) {
                        $posts[] = $product;
                        $message = PRODUCT_FETCHED_SUCCESSFULLY;
                    } else {
                        $status = FAILED;
                        $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                } else {
                    $history_array = ['user_id' => $user_id, 'product_id' => $product_id, 'created_date' => $current_date,'is_test' => IS_TESTDATA];
                    $add_history_response = addData(
                        $connection, '',
                        TABLE_HISTORY,
                        $history_array);
                    if ($add_history_response[STATUS_KEY] == SUCCESS) {
                        $posts[] = $product;
                        $message = PRODUCT_FETCHED_SUCCESSFULLY;
                    } else {
                        $status = FAILED;
                        $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                }
            }
        }
        else {
            if ($flag == 0)
            {
                $url = "https://ssl-api.openfoodfacts.org/cgi/search.pl?search_simple=1&json=1&action=process&fields=product_name,ingredients_text,codes_tags,image_url,nutriments,code&search_terms=" . urlencode($product_name) . "&page=1";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_URL, $url);

                $result = curl_exec($ch);
                curl_close($ch);
                $tempArr = json_decode($result, true);
                $temp = -1;
                $skip = false;
                $selected_index = -1;

                if(count($tempArr['products']) >0)
                {
                    foreach ($tempArr['products'] as $key => $value) {
                        $temp++;
                        if (!$skip && ($value["product_name"] == $product_name ||
                            $this->startsWith($value["product_name"],$product_name))){
                            $selected_index = $temp;
                            $skip = true;
                        }
                    }
                    if ($selected_index >= 0) {
                        $value = $tempArr['products'][$selected_index];
                    } else {
                        $value = $tempArr['products'][0];
                    }
                    $product_array = [
                        'barcode_id' => ($value['code']?$value['code']:''),
                        'product_name' => ($value['product_name']?$value['product_name']:''),
                        'is_delete' => IS_DELETE,
                        'is_test' => IS_TESTDATA,
                        'ingredients' => ($value['ingredients_text']?$value['ingredients_text']:''),
                        'saturated_fats' => ($value["nutriments"]["saturated-fat"]?$value["nutriments"]["saturated-fat"]:0),
                        'protein' => ($value["nutriments"]["proteins"]?$value["nutriments"]["proteins"]:0),
                        'sugar' => ($value["nutriments"]["sugars"]?$value["nutriments"]["sugars"]:0),
                        'salt' => ($value["nutriments"]["salt"]?$value["nutriments"]["salt"]:0),
                        'carbohydrate' => ($value["nutriments"]["carbohydrates"]?$value["nutriments"]["carbohydrates"]:0),
                        'dietary_fiber' => ($value["nutriments"]["fiber_value"]?$value["nutriments"]["fiber_value"]:0),
                        'sodium' => ($value["nutriments"]["sodium"]?$value["nutriments"]["sodium"]:0)];
                        if(!empty($product_array))
                        {
                            if (array_key_exists("image_url", $value))
                            {
                                $product_array['product_image'] = ($value["image_url"]?$value["image_url"]:'');//validateValue($value['image_url'], "");
                            }
                            if (array_key_exists("fat_amount", $value)) {

                                $product_array['fat_amount'] = ($value["nutriments"]["fat_amount"]?$value["nutriments"]["fat_amount"]:'');//$value["nutriments"]["fat_amount"];
                            }
                            $product_array['is_test'] = IS_TESTDATA;
                            $conditional_array_product = ['barcode_id' => $product_array['barcode_id'], 'is_delete' => IS_DELETE];
                            $objProductData = getSingleTableData(
                                $connection,
                                TABLE_PRODUCT, "",
                                "barcode_id", "",
                                $conditional_array_product);
                            if (!empty($objProductData)) {
                                $select = "select * from " . TABLE_PRODUCT . " where barcode_id= '" .$product_array['barcode_id']. "' and is_delete = '" .IS_DELETE. "'";
                                if ($stmt = $this->connection->prepare($select)) {
                                    if ($stmt->execute()) {
                                        if ($stmt->rowCount() > 0) {
                                            $status = SUCCESS;
                                            while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $product['is_favourite'] = 0;
                                                $posts[] = $product;
                                            }
                                        }
                                    }
                                    $stmt->closeCursor();
                                    $message = PRODUCT_FETCHED_SUCCESSFULLY;
                                }
                            } else {
                                 $insert_response = addData(
                                     $connection, '',
                                     TABLE_PRODUCT,
                                     $product_array);
                                    if ($insert_response[STATUS_KEY] == SUCCESS) {
                                        $last_inserted_id = $insert_response[MESSAGE_KEY];
                                        //Insert data into history
                                        $history_array = ['user_id' => $user_id, 'product_id' => $last_inserted_id, 'created_date' => $current_date];
                                        $add_history_response = addData(
                                            $connection, '',
                                            TABLE_HISTORY,
                                            $history_array);
                                        $select = "select * from " . TABLE_PRODUCT . " where id=" . $last_inserted_id;
                                        if ($stmt = $this->connection->prepare($select)) {
                                            if ($stmt->execute()) {
                                                if ($stmt->rowCount() > 0) {
                                                    $status = SUCCESS;
                                                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        $product['is_favourite'] = 0;
                                                        $posts[] = $product;
                                                    }
                                                }
                                            }
                                        $stmt->closeCursor();
                                        $message = PRODUCT_FETCHED_SUCCESSFULLY;
                                        }
                                    }
                                    else{
                                        $message = $insert_response[MESSAGE_KEY];
                                    }
                                }
                            }
                        else{
                            $message = NO_PRODUCT_AVAILABLE;
                        }
                    }
                    else{
                        $message = NO_PRODUCT_AVAILABLE;
                    }
                }
                else
                {
                    $url = "https://world.openfoodfacts.org/api/v0/product/" . urlencode($product_name) . ".json";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_URL, $url);

                    $result = curl_exec($ch);
                    curl_close($ch);
                    $tempArr = json_decode($result, true);
                    $temp = -1;
                    $skip = false;
                    $selected_index = -1;

                    if(count($tempArr['product']) >0)
                    {
                        $value = $tempArr['product'];
                        $product_array = [
                            'product_name' => ($value['product_name']?$value['product_name']:''),
                            'barcode_id' => ($tempArr['code']?$value['code']:''),
                            'is_delete' => IS_DELETE,
                            'is_test' => IS_TESTDATA,
                            'ingredients' => ($value['ingredients_text']?$value['ingredients_text']:''),
                            'saturated_fats' => ($value["nutriments"]["saturated-fat"]?$value["nutriments"]["saturated-fat"]:0),
                            'protein' => ($value["nutriments"]["proteins"]?$value["nutriments"]["proteins"]:0),
                            'sugar' => ($value["nutriments"]["sugars"]?$value["nutriments"]["sugars"]:0),
                            'salt' => ($value["nutriments"]["salt"]?$value["nutriments"]["salt"]:0),
                            'carbohydrate' => ($value["nutriments"]["carbohydrates"]?$value["nutriments"]["carbohydrates"]:0),
                            'dietary_fiber' => ($value["nutriments"]["fiber_value"]?$value["nutriments"]["fiber_value"]:0),
                            'sodium' => ($value["nutriments"]["sodium"]?$value["nutriments"]["sodium"]:0)
                            ];

                        if(!empty($product_array))
                        {
                            if (array_key_exists("image_url", $value)){
                                $product_array['product_image'] = ($value["image_url"]?$value["image_url"]:'');
                            }
                            if (array_key_exists("fat_amount", $value)) {
                                $product_array['fat_amount'] = ($value["nutriments"]["fat_amount"]?$value["nutriments"]["fat_amount"]:'');
                            }
                            $product_array['is_test'] = IS_TESTDATA;
                            if ($value['product_name'] != '')
                            {
                                $conditional_array_product = ['barcode_id' => $product_array['barcode_id'], 'is_delete' => IS_DELETE];
                                $objProductData = getSingleTableData(
                                    $connection,
                                    TABLE_PRODUCT, "",
                                    "barcode_id", "",
                                    $conditional_array_product);
                                if (!empty($objProductData)) {
                                    $select = "select * from " . TABLE_PRODUCT . " where barcode_id= '" .$product_array['barcode_id']. "' and is_delete = '" .IS_DELETE. "'";
                                    if ($stmt = $this->connection->prepare($select)) {
                                        if ($stmt->execute()) {
                                            if ($stmt->rowCount() > 0) {
                                                $status = SUCCESS;
                                                while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $product['is_favourite'] = 0;
                                                    $posts[] = $product;
                                                }
                                            }
                                        }
                                        $stmt->closeCursor();
                                        $message = PRODUCT_FETCHED_SUCCESSFULLY;
                                    }
                                }
                                else {
                                 $insert_response = addData(
                                     $connection, '',
                                     TABLE_PRODUCT,
                                     $product_array);
                                    if ($insert_response[STATUS_KEY] == SUCCESS) {
                                        $last_inserted_id = $insert_response[MESSAGE_KEY];
                                        //Insert data into history
                                        $history_array = ['user_id' => $user_id, 'product_id' => $last_inserted_id, 'created_date' => $current_date];
                                        $add_history_response = addData($connection, '', TABLE_HISTORY, $history_array);
                                        $select = "select * from " . TABLE_PRODUCT . " where id=" . $last_inserted_id;
                                        if ($stmt = $this->connection->prepare($select)) {
                                            if ($stmt->execute()) {
                                                if ($stmt->rowCount() > 0) {
                                                    $status = SUCCESS;
                                                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        $product['is_favourite'] = 0;
                                                        $posts[] = $product;
                                                    }
                                                }
                                            }
                                            $stmt->closeCursor();
                                            $message = PRODUCT_FETCHED_SUCCESSFULLY;
                                        }
                                    }
                                    else{
                                        $message = $insert_response[MESSAGE_KEY];
                                    }
                                }
                            }
                            else{
                                $message = NO_PRODUCT_AVAILABLE;
                            }
                        }
                        else{
                            $message = NO_PRODUCT_AVAILABLE;
                        }
                    }
                    else{
                        $message = NO_PRODUCT_AVAILABLE;
                    }
                }
        }
        $select_product_details_stmt->closeCursor();
        $data['status'] = $status;
        $data['message'] = $message;
        $data['product'] = $posts;
        return $data;
    }

     public function removeProductFromHistory($userData)
    {
        $connection = $this->connection;
        $history_id = validateObject($userData, 'history_id', "");
        $history_id = addslashes($history_id);

        $edit_history_response = editData(
            $connection,
            "removeProductFromHistory",
            TABLE_HISTORY,
            ['is_delete' => DELETE_STATUS::IS_DELETE],
            ['id' => $history_id , 'is_test' =>IS_TESTDATA],
            "");
        if ($edit_history_response[STATUS_KEY] === SUCCESS) {
            $objHistory = getSingleTableData(
                $connection,
                TABLE_HISTORY, '',
                '*', '',
                ['id' => $history_id,'is_test' =>IS_TESTDATA,'is_delete' => IS_DELETE]);
            if (!empty($objHistory)) {
                $conditional_array = ['product_id' => $objHistory['product_id'], 'user_id' => $objHistory['user_id'],'is_test' =>IS_TESTDATA];
                editData(
                    $connection,
                    "addToFavourite",
                    TABLE_FAVOURITE,
                    ['is_favourite' => $is_favourite = '0'],
                    $conditional_array,
                    "");
            }
            $message = HISTORY_REMOVED_SUCCESSFULLY;
            $status = SUCCESS;
        } else {
            $status = FAILED;
            $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
        }
        $data['status'] = $status;
        $data['message'] = $message;
        return $data;
    }

    public function getUserHistory($userData)
    {
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $to_index = validateObject($userData, 'to_index', "");
        $to_index = addslashes($to_index);

        $from_index = validateObject($userData, 'from_index', "");
        $from_index = addslashes($from_index);

        $posts = [];

        $select_user_history_query = "SELECT h.id as history_id, h.user_id, h.product_id, h.created_date AS history_created_date , p.* FROM history AS h
                                      LEFT JOIN product AS p ON p.id = h.product_id
                                      WHERE h.user_id = :user_id AND h.is_delete = :is_delete
                                    AND p.is_delete = :is_delete AND h.is_test = '".IS_TESTDATA."'  
                                    AND p.product_name != '' ORDER BY h.created_date DESC limit $from_index,$to_index ";
        $conditional_array = ['user_id' => $user_id, 'is_delete' => IS_DELETE];
        $select_user_history_stmt = getMultipleTableData(
            $connection, "",
            $select_user_history_query, "", "",
            $conditional_array);
        if ($select_user_history_stmt->rowCount() > 0) {
            while ($history = $select_user_history_stmt->fetch(PDO::FETCH_ASSOC)) {
                $select_total_review_query = "Select count(*) as total_review, avg(ratting) as avg_review from ". TABLE_REVIEW ." r where product_id = '".$history['product_id']."' and is_test = '".IS_TESTDATA."' and is_delete = '".IS_DELETE."'";
                $select_total_review_stmt = getSingleTableData(
                    $connection,"",
                    $select_total_review_query,"","","");
                if (!empty($select_total_review_stmt)) {
                    $history['total_review'] = $select_total_review_stmt['total_review'];
                    $history['avg_review'] = $select_total_review_stmt['avg_review'];
                }
                //get user favourite
                $is_favourite = 1;
                $conditional_array = ['product_id' => $history['product_id'], 'user_id' => $user_id, 'is_favourite' => $is_favourite, 'is_delete' => IS_DELETE,'is_test' =>IS_TESTDATA];
                $objFavourite = getSingleTableData(
                    $connection,
                    TABLE_FAVOURITE, "",
                    "id", "",
                    $conditional_array);
                if (!empty($objFavourite)) {
                    $history['is_favourite'] = "1";
                } else {
                    $history['is_favourite'] = "0";
                }
                $posts[] = $history;
            }
            $message = DATA_FETCHED_SUCCESSFULLY;
            $status = SUCCESS;
        } else {
            $status = SUCCESS;
            $message = NO_FAVOURITE_HISTORY_FOUND;
        }
        $data['status'] = $status;
        $data['message'] = $message;
        $data['history'] = $posts;
        return $data;
    }

    public function getAllUserFavourite($userData)
    {
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $to_index = validateObject($userData, 'to_index', "");
        $to_index = addslashes($to_index);

        $from_index = validateObject($userData, 'from_index', "");
        $from_index = addslashes($from_index);

        $posts = [];
        $is_favourite = 1;

        $select_user_favourite_query = "SELECT f.id as favourite_id , f.user_id, f.product_id, f.is_favourite, f.created_date AS favourite_created_date , p.* FROM " . TABLE_FAVOURITE . " AS f
                                        LEFT JOIN " . TABLE_PRODUCT . " AS p ON p.id = f.product_id
                                        WHERE f.user_id = ".$user_id." AND f.is_favourite = '".$is_favourite."'
                                        AND f.is_test = '".IS_TESTDATA."'  AND f.is_delete = '".IS_DELETE."' 
                                        AND p.is_delete = '".IS_DELETE."'
                                        AND p.product_name != '' ORDER BY f.created_date DESC limit $from_index,$to_index ";
        $select_user_favourite_stmt = getMultipleTableData(
            $connection, "",
            $select_user_favourite_query,
            "", "", []);
        if ($select_user_favourite_stmt->rowCount() > 0) {
            while ($product = $select_user_favourite_stmt->fetch(PDO::FETCH_ASSOC)) {
                $select_total_review_query = "Select count(*) as total_review, avg(ratting) as avg_review from ". TABLE_REVIEW ." r 
                where product_id = '".$product['product_id']."' 
                and is_test = '".IS_TESTDATA."' and is_delete = '".IS_DELETE."'";
                $select_total_review_stmt = getSingleTableData(
                    $connection,"",
                    $select_total_review_query,
                    "","","");
                if (!empty($select_total_review_stmt)) {
                    $product['total_review'] = $select_total_review_stmt['total_review'];
                    $product['avg_review'] = $select_total_review_stmt['avg_review'];
                }
                $posts[] = $product;
            }
            $status = SUCCESS;
            $message = DATA_FETCHED_SUCCESSFULLY;
        } else {
            $status = SUCCESS;
            $message = NO_FAVOURITE_PRODUCT_FOUND;
        }
        $data['status'] = $status;
        $data['message'] = $message;
        $data['product'] = $posts;
        return $data;
    }

    public function addToFavourite($userData)
    {
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $product_id = validateObject($userData, 'product_id', "");
        $product_id = addslashes($product_id);

        $is_favourite = validateObject($userData, 'is_favourite', "");
        $is_favourite = addslashes($is_favourite);

        $is_rate = false;
        $current_date = date(DATETIME_FORMAT);

        $conditional_array = ['product_id' => $product_id, 'user_id' => $user_id, 'is_delete' => IS_DELETE,'is_test' => IS_TESTDATA];
        $objFavourite = getSingleTableData(
            $connection,
            TABLE_FAVOURITE, "",
            "id,is_favourite", "",
            $conditional_array);

        if (!empty($objFavourite)) {
            $edit_response = editData(
                $connection,
                "addToFavourite",
                TABLE_FAVOURITE,
                ['is_favourite' => $is_favourite, 'created_date' => $current_date],
                ['id' => $objFavourite['id']], "");
            if ($edit_response[STATUS_KEY] === SUCCESS) {
                $status = SUCCESS;
                $message = $is_favourite == 1 ? FAVOURITE_SUCCESSFULLY : REMOVE_FAVOURITE_SUCCESSFULLY;
            } else {
                $status = FAILED;
                $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            }
        } else {
            $favourite_product_array = ['user_id' => $user_id, 'product_id' => $product_id, 'is_favourite' => $is_favourite, 'created_date' => $current_date,'is_test' => IS_TESTDATA];
            $favourite_response = addData(
                $connection,
                "addToFavourite",
                TABLE_FAVOURITE,
                $favourite_product_array);
            if ($favourite_response[STATUS_KEY] == SUCCESS) {
                $status = SUCCESS;
                $message = FAVOURITE_SUCCESSFULLY;
            } else {
                $status = FAILED;
                $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            }
        }
        //START : Check if user have added 5 or more product as favourite then  rate status will as true else false.
        if  ($status == SUCCESS) {
            $rate_status = $this->IsUserRate($user_id);
            if ($rate_status == SUCCESS) {
                $select_user_fav_query = "SELECT count(*) as count_fav from " . TABLE_FAVOURITE . " WHERE user_id = " . $user_id . " 
                                    AND is_favourite = '1'
                                    AND is_test = '" . IS_TESTDATA . "' AND is_delete = '" . IS_DELETE . "' ";
                $select_user_fav_stmt = getSingleTableData(
                    $connection, "",
                    $select_user_fav_query, "", "", []);
                if (!empty($select_user_fav_stmt) && $select_user_fav_stmt['count_fav'] >= 5) {
                    $is_rate = true;
                } else {
                    $is_rate = false;
                }
            } else {
                $is_rate = false;
            }
        }
        //END
        $data['is_rate'] = $is_rate;
        $data['status'] = $status;
        $data['message'] = $message;
        return $data;
    }

    public function  IsUserRate($user_id)
    {
        $connection = $this->connection;
        $select_user_rated_query = "SELECT * from ".TABLE_RATING." WHERE user_id = ".$user_id." 
                                    AND is_rate = '1'
                                    AND device_type = (SELECT device_type from ".TABLE_USER." WHERE id = ".$user_id." AND is_test = '".IS_TESTDATA."' and is_delete = '".IS_DELETE."') 
                                    AND is_test = '".IS_TESTDATA."' AND is_delete = '".IS_DELETE."' ";
        $select_user_rated_stmt = getSingleTableData(
            $connection, "",
            $select_user_rated_query, "", "", []);
        if (!empty($select_user_rated_stmt)){
            $status = FAILED;
        }else{
            $status = SUCCESS;
        }
        return $status;
    }

}
