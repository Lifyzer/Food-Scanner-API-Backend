<?php
/**
 * Created by PhpStorm.
 * User: c157
 * Date: 18/01/18
 * Time: 10:56 AM
 */

class ProductFunctions
{
    protected $connection;
    function __construct(PDO $con)
    {
        $this->connection = $con;
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

            default:
                return null;
                break;
        }
    }

    public function getProductDetails($userData)
    {
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', "");
        $user_id = addslashes($user_id);

        $product_name = validateObject($userData, 'product_name', "");
        $product_name = addslashes($product_name);

        $posts = array();

        $is_delete = IS_DELETE;
        $current_date = getDefaultDate();

        $select_product_details_stmt=getMultipleTableData($connection,TABLE_PRODUCT,"","*",
            "LOWER(product_name) = LOWER('".$product_name."') AND is_delete ='".$is_delete."' ORDER BY created_date LIMIT 1","");
        if($select_product_details_stmt->rowCount()>0){

            while ($product=$select_product_details_stmt->fetch(PDO::FETCH_ASSOC)){
                //******************* get user favourite ****************//
                $is_favourite = 1;
                $conditional_array=array('product_id'=>$product['id'],'user_id'=>$user_id,'is_favourite'=>$is_favourite,'is_delete'=>$is_delete);
                $objFavourite=getSingleTableData($connection,TABLE_FAVOURITE,"","id","",$conditional_array);
//                echo $product['id']."-2-".$user_id."-3-".$is_favourite."-4-".$is_delete;
                if(!empty($objFavourite)){
                    $product['is_favourite'] = 1;
                }
                else{
                    $product['is_favourite'] = 0;
                }
                //**** Product found in database insert data into history table ****//
                $product_id = $product['id'];
                $conditional_array=array('product_id'=>$product_id,'user_id'=>$user_id,'is_delete'=>$is_delete);
                $objHistory=getSingleTableData($connection,TABLE_HISTORY,"","id","",$conditional_array);
                if(!empty($objHistory)){
                    //******** Update history ********//
                    $history_id = $objHistory['id'];
                    $edit_history_response=editData($connection,"getProductDetails",TABLE_HISTORY,array('created_date'=>$current_date),array('id'=>$history_id),"");
                    if($edit_history_response[STATUS_KEY]==SUCCESS){
                        $posts[] = $product;
                    }
                    else{
                        $status=FAILED;
                        $message=SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                }
                else{
                    //******** Insert data into history ********//
                    $history_array=array('user_id'=>$user_id,'product_id'=>$product_id,'created_date'=>$current_date);
                    $add_history_response=addData($connection,"getProductDetails",TABLE_HISTORY,$history_array);
                    if($add_history_response[STATUS_KEY]==SUCCESS){
                        $posts[]=$product;
                    }
                    else{
                        $status=FAILED;
                        $message=SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                }
            }
            $message=DATA_FETCHED_SUCCESSFULLY;
            $status=SUCCESS;
        }
        else{
            $status=SUCCESS;
            $message=NO_PRODUCT_FOUND_IN_DATABASE;
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
        $is_delete = DELETE_STATUS::IS_DELETE;
        $edit_history_response=editData($connection,"removeProductFromHistory",TABLE_HISTORY,array('is_delete'=>$is_delete),array('id'=>$history_id),"");
        if($edit_history_response[STATUS_KEY]==SUCCESS){
            $objHistory=getSingleTableData($connection,TABLE_HISTORY,"","*","",array('id'=>$history_id));
            if(!empty($objHistory)){
                $conditional_array=array('product_id'=>$objHistory['product_id'],'user_id'=>$objHistory['user_id'],'is_delete'=>$is_delete);
                editData($connection,"addToFavourite",TABLE_FAVOURITE,array('is_favourite'=>$is_favourite='0'),$conditional_array,"");
            }
            $message=HISTORY_REMOVED_SUCCESSFULLY;
            $status=SUCCESS;
        }
        else{
            $status=FAILED;
            $message=SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
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

        $posts = array();
        $is_delete = IS_DELETE;
        $select_user_history_query = "select h.id as history_id, h.user_id, h.product_id, h.created_date as history_created_date , p.* from history as h
                                      left JOIN product as p on p.id = h.product_id
                                      WHERE h.user_id = :user_id and h.is_delete = :is_delete ORDER BY h.created_date DESC limit $from_index,$to_index ";
        $conditional_array=array('user_id'=>$user_id,'is_delete'=>$is_delete);
        $select_user_history_stmt=getMultipleTableData($connection,"",$select_user_history_query,"","",$conditional_array);
        if($select_user_history_stmt->rowCount()>0){
            while ($history=$select_user_history_stmt->fetch(PDO::FETCH_ASSOC)){
                //******************* get user favourite ****************//
                $is_favourite = 1;
                $conditional_array=array('product_id'=>$history['product_id'],'user_id'=>$user_id,'is_favourite'=>$is_favourite,'is_delete'=>$is_delete);
                $objFavourite=getSingleTableData($connection,TABLE_FAVOURITE,"","id","",$conditional_array);
                if(!empty($objFavourite)){
                    $history['is_favourite'] = 1;
                }
                else{
                    $history['is_favourite'] = 0;
                }
                $posts[] = $history;
            }
                $message=DATA_FETCHED_SUCCESSFULLY;
            $status=SUCCESS;
        }
        else{
            $status=SUCCESS;
            $message=NO_FAVOURITE_HISTORY_FOUND;
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

        $posts = array();

        $is_delete = IS_DELETE;
        $is_favourite = "1";

        $select_user_favourite_query = "SELECT f.id as favourite_id , f.user_id, f.product_id, f.is_favourite, f.created_date as favourite_created_date , p.*FROM " . TABLE_FAVOURITE . " as f
                                        left join " . TABLE_PRODUCT . " as p on p.id = f.product_id
                                        where f.user_id = :user_id and f.is_favourite = :is_favourite and f.is_delete = :is_delete ORDER BY f.created_date DESC limit $from_index,$to_index  ";

        $select_user_favourite_stmt=getMultipleTableData($connection,"",$select_user_favourite_query,"","",array('user_id'=>$user_id,'is_favourite'=>$is_favourite,'is_delete'=>$is_delete));

        if($select_user_favourite_stmt->rowCount()>0){
            while ($product=$select_user_favourite_stmt->fetch(PDO::FETCH_ASSOC)){
                $posts[] = $product;
            }
            $status=SUCCESS;
            $message=DATA_FETCHED_SUCCESSFULLY;
        }
        else{
            $status=SUCCESS;
            $message=NO_FAVOURITE_PRODUCT_FOUND;
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

        $is_delete = IS_DELETE;
        $current_date = date("Y-m-d H:i:s");

        $conditional_array=array('product_id'=>$product_id,'user_id'=>$user_id,'is_delete'=>$is_delete);
        $objFavourite=getSingleTableData($connection,TABLE_FAVOURITE,"","id,is_favourite","",$conditional_array);
        if(!empty($objFavourite)){
            $edit_response=editData($connection,"addToFavourite",TABLE_FAVOURITE,array('is_favourite'=>$is_favourite,'created_date'=>$current_date),array('id'=>$objFavourite['id']),"");

            if($edit_response[STATUS_KEY]==SUCCESS){
                $status=SUCCESS;
                if($is_favourite==1)
                    $message="Product added in favourite.";
                else
                    $message="Product remove from favourite.";
            }
            else{
                $status=FAILED;
                $message=SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            }
        }
        else{
            $favourite_product_array=array('user_id'=>$user_id,'product_id'=>$product_id,'is_favourite'=>$is_favourite,'created_date'=>$current_date);
            $favourite_response=addData($connection,"addToFavourite",TABLE_FAVOURITE,$favourite_product_array);
            if($favourite_response[STATUS_KEY]==SUCCESS){
                $status=SUCCESS;
                $message=FAVOURITE_SUCCESSFULLY;
            }
            else{
                $status=FAILED;
                $message=SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            }
        }
        $data['status'] = $status;
        $data['message'] = $message;
        return $data;
    }
}

?>
