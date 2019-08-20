<?php

namespace Lifyzer\Api;

use PDO;
use phpFastCache\Helper\Psr16Adapter;

class Product
{
    private const CACHE_LIFETIME = 3600 * 24;
    private const CACHE_DRIVER = 'Files';

    protected $connection;

    public function __construct(PDO $con)
    {
        $this->connection = $con;
    }

    public function callService($service, $postData)
    {
        switch ($service) {
            case 'addToFavourite':
                return $this->addToFavourite($postData);

            case 'getAllUserFavourite':
                return $this->getAllUserFavourite($postData);

            case 'getProductDetails':
                return $this->getProductDetails2($postData);

            case 'getProductDetailsTest':
                return $this->getProductDetailsTest($postData);

            case 'getUserHistory':
                return $this->getUserHistory($postData);

            case 'removeProductFromHistory':
                return $this->removeProductFromHistory($postData);

            default:
                return null;
        }
    }

    /**
     * @param mixed $userData
     *
     * @return mixed
     *
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function getProductDetails($userData)
    {

        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', '');
        $user_id = addslashes($user_id);

        $product_name = validateObject($userData, 'product_name', '');

        $is_foodfact = validateObject($userData, 'is_foodfact', '');
        $is_foodfact = addslashes($is_foodfact);

        $posts = [];

        if ($is_foodfact == "1") {

            //$url="https://ssl-api.openfoodfacts.org/cgi/search.pl?search_simple=1&json=1&action=process&search_terms=Mini%20crackers%20Tomato,%20Onion%20&%20Chili&page=1";
            $url = "https://ssl-api.openfoodfacts.org/cgi/search.pl?search_simple=1&json=1&action=process&fields=product_name,ingredients_text,codes_tags,image_url,nutriments&search_terms=Mini%20crackers%20Tomato,%20Onion%20&%20Chili&page=1";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
            $tempArr = json_decode($result, true);

            $newArr = array();

            $newArr['status'] = SUCCESS;
            $newArr['message'] = "";

            foreach ($tempArr['products'] as $key => $value) {

                // $product = [];

                $product['id'] = null;
                $product['barcode_id'] = null;
                $product['company_name'] = null;
                $product['calories'] = null;

                $product['product_name'] = $value["product_name"];
                $product['product_name'] = $value["product_name"];
                $product['product_image'] = $value["image_url"];
                $product['ingredients'] = $value["ingredients_text"];
                $product['saturated_fats'] = $value["nutriments"]["saturated-fat"];
                $product['fat_amount'] = $value["nutriments"]["fat_amount"];
                $product['carbohydrate'] = $value["nutriments"]["carbohydrates"];
                $product['sugar'] = $value["nutriments"]["sugars"];
                $product['dietary_fiber'] = $value["nutriments"]["fiber_value"];
                $product['protein'] = $value["nutriments"]["proteins"];
                $product['protein_amount'] = "";
                $product['salt'] = $value["nutriments"]["salt"];
                $product['sodium'] = $value["nutriments"]["sodium"];

                $product['created_date'] = null;
                $product['modified_date'] = null;
                $product['is_delete'] = "0";
                $product['is_test'] = "";
                $product['is_organic'] = null;
                $product['is_healthy'] = null;
                $product['is_favourite'] = null;
                $product['alcohol'] = null;
                $product['license_no'] = null;
                $product['category_id'] = "0";

                $newArr['product'][] = $product;

            }

            return $newArr;

        }


        $is_delete = IS_DELETE;
        $current_date = getDefaultDate();

        $cacher = new Psr16Adapter(self::CACHE_DRIVER);
        $cacheKey = 'productdetails' . $product_name;

        if (!$cacher->has($cacheKey)) {

            $select_product_details_stmt =
                getMultipleTableData(
                    $connection,
                    TABLE_PRODUCT,
                    '',
                    '*',
                    '(LOWER(product_name) LIKE LOWER(:product_name) OR barcode_id = :barcode) AND is_delete = :is_delete ORDER BY created_date LIMIT 1',
                    [
                        'product_name' => '%' . $product_name . '%',
                        'barcode' => $product_name,
                        'is_delete' => $is_delete
                    ]
                );

            //$cacher->set($cacheKey, $select_product_details_stmt, self::CACHE_LIFETIME);

            //echo "Row count : " .$select_product_details_stmt->rowCount() > 0;
        } else {
            $select_product_details_stmt = $cacher->get($cacheKey);
        }

        if ($select_product_details_stmt->rowCount() > 0) {
            $status = SUCCESS;

            while ($product = $select_product_details_stmt->fetch(PDO::FETCH_ASSOC)) {


                //******************* get user favourite ****************//
                $is_favourite = 1;
                $conditional_array = ['product_id' => $product['id'], 'user_id' => $user_id, 'is_favourite' => $is_favourite, 'is_delete' => $is_delete];
                $objFavourite = getSingleTableData($connection, TABLE_FAVOURITE, "", "id", "", $conditional_array);
//                echo $product['id']."-2-".$user_id."-3-".$is_favourite."-4-".$is_delete;
                if (!empty($objFavourite)) {
                    $product['is_favourite'] = 1;
                } else {
                    $product['is_favourite'] = 0;
                }

                //**** Product found in database insert data into history table ****//
                $product_id = $product['id'];
                $conditional_array = ['product_id' => $product_id, 'user_id' => $user_id, 'is_delete' => $is_delete];
                $objHistory = getSingleTableData($connection, TABLE_HISTORY, "", "id", "", $conditional_array);

                if (!empty($objHistory)) {

                    //******** Update history ********//
                    $history_id = $objHistory['id'];
                    $edit_history_response = editData($connection, 'getProductDetails', TABLE_HISTORY, ['created_date' => $current_date], ['id' => $history_id], "");
                    if ($edit_history_response[STATUS_KEY] == SUCCESS) {
                        $posts[] = $product;
                    } else {
                        $status = FAILED;
                        $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                } else {

                    //******** Insert data into history ********//
                    $history_array = ['user_id' => $user_id, 'product_id' => $product_id, 'created_date' => $current_date];
                    $add_history_response = addData($connection, 'getProductDetails', TABLE_HISTORY, $history_array);
                    if ($add_history_response[STATUS_KEY] == SUCCESS) {
                        $posts[] = $product;
                    } else {
                        $status = FAILED;
                        $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                }
            }
        } else {

            $status = SUCCESS;
            $message = NO_PRODUCT_FOUND_IN_DATABASE;
        }

        $select_product_details_stmt->closeCursor();
        $data['status'] = $status;
        $data['message'] = $message;
        $data['product'] = $posts;

        return $data;
    }

    function startsWith($haystack, $needle)
	{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
	}

	public function getProductDetailsTest($userData)
    {
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', '');
        $user_id = addslashes($user_id);

        $product_name = validateObject($userData, 'product_name', '');
		$product_name = utf8_decode($product_name);

        $is_foodfact = validateObject($userData, 'is_foodfact', '');
        $is_foodfact = addslashes($is_foodfact);

        $is_testdata = validateObject($userData, 'is_testdata', 1);
        $is_testdata = addslashes($is_testdata);

		$flag = validateObject($userData, 'flag', 0);
        $flag = addslashes($flag);

        $posts = [];

        $is_delete = IS_DELETE;
        $current_date = getDefaultDate();

        $message = "";
        $status = FAILED;

        $cacher = new Psr16Adapter(self::CACHE_DRIVER);
        $cacheKey = 'productDetails' . $product_name;


        if (!$cacher->has($cacheKey)) {

            $select_product_details_stmt =

                getMultipleTableData(
                    $connection,
                    TABLE_PRODUCT,
                    '',
                    '*',
                    '(LOWER(product_name) LIKE LOWER(:product_name) OR barcode_id = :barcode) AND is_delete = :is_delete ORDER BY created_date LIMIT 1',
                    [
                        'product_name' => $product_name.'%', //'%' . $product_name . '%',
                        'barcode' => $product_name,
                        'is_delete' => $is_delete
                    ]
                );

        } else {
            $select_product_details_stmt = $cacher->get($cacheKey);
        }


        if ($select_product_details_stmt->rowCount() > 0) {
            $status = SUCCESS;

            while ($product = $select_product_details_stmt->fetch(PDO::FETCH_ASSOC)) {


                //******************* get user favourite ****************//
                $is_favourite = 1;
                $conditional_array = ['product_id' => $product['id'], 'user_id' => $user_id, 'is_favourite' => $is_favourite, 'is_delete' => $is_delete];
                $objFavourite = getSingleTableData($connection, TABLE_FAVOURITE, "", "id", "", $conditional_array);

                if (!empty($objFavourite)) {
                    $product['is_favourite'] = 1;
                } else {
                    $product['is_favourite'] = 0;
                }

                //**** Product found in database insert data into history table ****//
                $product_id = $product['id'];
                $conditional_array = ['product_id' => $product_id, 'user_id' => $user_id, 'is_delete' => $is_delete];

                $objHistory = getSingleTableData($connection, TABLE_HISTORY, "", "id", "", $conditional_array);

                if (!empty($objHistory)) {

                    //******** Update history ********//
                    $history_id = $objHistory['id'];

                    $edit_history_response = editData($connection, 'getProductDetailsTest', TABLE_HISTORY, ['created_date' => $current_date], ['id' => $history_id], "");
                    if ($edit_history_response[STATUS_KEY] == SUCCESS) {
                        $posts[] = $product;
                        $message = "Product successfully fetched";
                    } else {
                        $status = FAILED;
                        $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                } else {

                    //******** Insert data into history ********//
                    $history_array = ['user_id' => $user_id, 'product_id' => $product_id, 'created_date' => $current_date];

                    $add_history_response = addData($connection, '', TABLE_HISTORY, $history_array);
                    if ($add_history_response[STATUS_KEY] == SUCCESS) {
                        $posts[] = $product;
                        $message = "Product successfully fetched";
                    } else {
                        $status = FAILED;
                        $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                }
            }
        } else {

			if ($flag == 0)
			{

            //$url="https://ssl-api.openfoodfacts.org/cgi/search.pl?search_simple=1&json=1&action=process&search_terms=Mini%20crackers%20Tomato,%20Onion%20&%20Chili&page=1";
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

            if(count($tempArr['products']) >0)//(!empty($tempArr))
            {

				foreach ($tempArr['products'] as $key => $value) {

					$temp++;

					if (!$skip && ($value["product_name"] == $product_name || $this->startsWith($value["product_name"],$product_name))) {
						$selected_index = $temp;
						$skip = true;
					}

					/*if (!$skip && $pos === true) {
						$selected_index = $temp;
						$skip = true;
					}	*/
				}

				if ($selected_index >= 0) {

					$value = $tempArr['products'][$selected_index];

				} else {

					$value = $tempArr['products'][0];
				}

				//if(count($value["nutriments"]) > 0)
				//{

					 $product_array = [
					 'barcode_id' => ($value['code']?$value['code']:''),
					 'product_name' => ($value['product_name']?$value['product_name']:''),
					'is_delete' => ($is_delete?$is_delete:0),
					'is_test' => ($is_testdata?$is_testdata:0),
					'ingredients' => ($value['ingredients_text']?$value['ingredients_text']:''),
					'saturated_fats' => ($value["nutriments"]["saturated-fat"]?$value["nutriments"]["saturated-fat"]:0),
					'protein' => ($value["nutriments"]["proteins"]?$value["nutriments"]["proteins"]:0),
					'sugar' => ($value["nutriments"]["sugars"]?$value["nutriments"]["sugars"]:0),
					'salt' => ($value["nutriments"]["salt"]?$value["nutriments"]["salt"]:0),
					'carbohydrate' => ($value["nutriments"]["carbohydrates"]?$value["nutriments"]["carbohydrates"]:0),
					'dietary_fiber' => ($value["nutriments"]["fiber_value"]?$value["nutriments"]["fiber_value"]:0),
					'sodium' => ($value["nutriments"]["sodium"]?$value["nutriments"]["sodium"]:0),
					];
				//}

					if(!empty($product_array))
					{

						if (array_key_exists("image_url", $value))
						{

							$product_array['product_image'] = ($value["image_url"]?$value["image_url"]:'');//validateValue($value['image_url'], "");
						}

						if (array_key_exists("fat_amount", $value)) {

							$product_array['fat_amount'] = ($value["nutriments"]["fat_amount"]?$value["nutriments"]["fat_amount"]:'');//$value["nutriments"]["fat_amount"];
						}

						$insert_response = addData($connection, '', TABLE_PRODUCT, $product_array);
							if ($insert_response[STATUS_KEY] == SUCCESS) {
								$last_inserted_id = $insert_response[MESSAGE_KEY];


								//******** Insert data into history ********//
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
									$message = "Product successfully fetched";
								}
							}
							else
							{

								$message = $insert_response[MESSAGE_KEY];
							}
					}
					else
					{

						$message = "No Product Available";
					}

			/*	}
				else
				{

					$message = "No Product Available";
				}*/

            }
            else
            {
            	$message = "No Products Available";
            }
            }
            else
            {

		//	https://world.openfoodfacts.org/api/v0/product/20218775.json
            $url = "https://world.openfoodfacts.org/api/v0/product/" . urlencode($product_name) . ".json";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_URL, $url);

            $result = curl_exec($ch);

           // print_r($result);
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
					'is_delete' => ($is_delete?$is_delete:0),
					'is_test' => ($is_testdata?$is_testdata:0),
					'ingredients' => ($value['ingredients_text']?$value['ingredients_text']:''),
					'saturated_fats' => ($value["nutriments"]["saturated-fat"]?$value["nutriments"]["saturated-fat"]:0),
					'protein' => ($value["nutriments"]["proteins"]?$value["nutriments"]["proteins"]:0),
					'sugar' => ($value["nutriments"]["sugars"]?$value["nutriments"]["sugars"]:0),
					'salt' => ($value["nutriments"]["salt"]?$value["nutriments"]["salt"]:0),
					'carbohydrate' => ($value["nutriments"]["carbohydrates"]?$value["nutriments"]["carbohydrates"]:0),
					'dietary_fiber' => ($value["nutriments"]["fiber_value"]?$value["nutriments"]["fiber_value"]:0),
					'sodium' => ($value["nutriments"]["sodium"]?$value["nutriments"]["sodium"]:0),
					];

					if(!empty($product_array))
					{

						if (array_key_exists("image_url", $value))
						{

							$product_array['product_image'] = ($value["image_url"]?$value["image_url"]:'');//validateValue($value['image_url'], "");
						}

						if (array_key_exists("fat_amount", $value)) {

							$product_array['fat_amount'] = ($value["nutriments"]["fat_amount"]?$value["nutriments"]["fat_amount"]:'');//$value["nutriments"]["fat_amount"];
						}

						$insert_response = addData($connection, '', TABLE_PRODUCT, $product_array);
							if ($insert_response[STATUS_KEY] == SUCCESS) {
								$last_inserted_id = $insert_response[MESSAGE_KEY];


								//******** Insert data into history ********//
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
									$message = "Product successfully fetched";
								}
							}
							else
							{
								$message = $insert_response[MESSAGE_KEY];
							}
					}
					else
					{

						$message = "No Product Available";
					}
            }
            else
            {
            	$message = "No Products Available";
            }
            }
        }

        $select_product_details_stmt->closeCursor();
        $data['status'] = $status;
        $data['message'] = $message;
        $data['product'] = $posts;

        return $data;
    }


   public function getProductDetails2($userData)
 	{
        $connection = $this->connection;

        $user_id = validateObject($userData, 'user_id', '');
        $user_id = addslashes($user_id);

        $product_name = validateObject($userData, 'product_name', '');
        $product_name = utf8_decode($product_name);

        $is_foodfact = validateObject($userData, 'is_foodfact', '');
        $is_foodfact = addslashes($is_foodfact);

        $is_testdata = validateObject($userData, 'is_testdata', 1);
        $is_testdata = addslashes($is_testdata);

        $flag = validateObject($userData, 'flag', 0);
        $flag = addslashes($flag);

        $is_barcode_scanned = validateObject($userData, 'is_barcode_scanned', 0);
        $is_barcode_scanned = addslashes($is_barcode_scanned);

        $posts = [];

        $is_delete = IS_DELETE;
        $current_date = getDefaultDate();

        $message = "";
        $status = FAILED;

        $cacher = new Psr16Adapter(self::CACHE_DRIVER);
        $cacheKey = 'productDetails' . $product_name;


        if (!$cacher->has($cacheKey)) {

            $select_product_details_stmt =

                getMultipleTableData(
                    $connection,
                    TABLE_PRODUCT,
                    '',
                    '*',
                    '(LOWER(product_name) LIKE LOWER(:product_name) OR barcode_id = :barcode) AND is_delete = :is_delete ORDER BY created_date LIMIT 1',
                    [
                        'product_name' => $product_name.'%', //'%' . $product_name . '%',
                        'barcode' => $product_name,
                        'is_delete' => $is_delete
                    ]
                );

        } else {
            $select_product_details_stmt = $cacher->get($cacheKey);
        }


        if ($select_product_details_stmt->rowCount() > 0) {
            $status = SUCCESS;

            while ($product = $select_product_details_stmt->fetch(PDO::FETCH_ASSOC)) {


                //******************* get user favourite ****************//
                $is_favourite = 1;
                $conditional_array = ['product_id' => $product['id'], 'user_id' => $user_id, 'is_favourite' => $is_favourite, 'is_delete' => $is_delete];
                $objFavourite = getSingleTableData($connection, TABLE_FAVOURITE, "", "id", "", $conditional_array);

                if (!empty($objFavourite)) {
                    $product['is_favourite'] = 1;
                } else {
                    $product['is_favourite'] = 0;
                }

                //**** Product found in database insert data into history table ****//
                $product_id = $product['id'];
                $conditional_array = ['product_id' => $product_id, 'user_id' => $user_id, 'is_delete' => $is_delete];

                $objHistory = getSingleTableData($connection, TABLE_HISTORY, "", "id", "", $conditional_array);

                if (!empty($objHistory)) {

                    //******** Update history ********//
                    $history_id = $objHistory['id'];

                    $edit_history_response = editData($connection, 'getProductDetailsTest', TABLE_HISTORY, ['created_date' => $current_date], ['id' => $history_id], "");
                    if ($edit_history_response[STATUS_KEY] == SUCCESS) {
                        $posts[] = $product;
                        $message = "Product successfully fetched";
                    } else {
                        $status = FAILED;
                        $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                } else {

                    //******** Insert data into history ********//
                    $history_array = ['user_id' => $user_id, 'product_id' => $product_id, 'created_date' => $current_date];

                    $add_history_response = addData($connection, '', TABLE_HISTORY, $history_array);
                    if ($add_history_response[STATUS_KEY] == SUCCESS) {
                        $posts[] = $product;
                        $message = "Product successfully fetched";
                    } else {
                        $status = FAILED;
                        $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
                        break;
                    }
                }
            }
        } else {

            if ($is_barcode_scanned == 0) {

            //$url="https://ssl-api.openfoodfacts.org/cgi/search.pl?search_simple=1&json=1&action=process&search_terms=Mini%20crackers%20Tomato,%20Onion%20&%20Chili&page=1";
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

            if(count($tempArr['products']) >0)//(!empty($tempArr))
            {

                foreach ($tempArr['products'] as $key => $value) {

                    $temp++;

                    if (!$skip && ($value["product_name"] == $product_name || $this->startsWith($value["product_name"],$product_name))) {
                        $selected_index = $temp;
                        $skip = true;
                    }

                    /*if (!$skip && $pos === true) {
                        $selected_index = $temp;
                        $skip = true;
                    }   */
                }

                if ($selected_index >= 0) {

                    $value = $tempArr['products'][$selected_index];

                } else {

                    $value = $tempArr['products'][0];
                }

                //if(count($value["nutriments"]) > 0)
                //{

                     $product_array = [
                     'barcode_id' => ($value['code']?$value['code']:''),
                     'product_name' => ($value['product_name']?$value['product_name']:''),
                    'is_delete' => ($is_delete?$is_delete:0),
                    'is_test' => ($is_testdata?$is_testdata:0),
                    'ingredients' => ($value['ingredients_text']?$value['ingredients_text']:''),
                    'saturated_fats' => ($value["nutriments"]["saturated-fat"]?$value["nutriments"]["saturated-fat"]:0),
                    'protein' => ($value["nutriments"]["proteins"]?$value["nutriments"]["proteins"]:0),
                    'sugar' => ($value["nutriments"]["sugars"]?$value["nutriments"]["sugars"]:0),
                    'salt' => ($value["nutriments"]["salt"]?$value["nutriments"]["salt"]:0),
                    'carbohydrate' => ($value["nutriments"]["carbohydrates"]?$value["nutriments"]["carbohydrates"]:0),
                    'dietary_fiber' => ($value["nutriments"]["fiber_value"]?$value["nutriments"]["fiber_value"]:0),
                    'sodium' => ($value["nutriments"]["sodium"]?$value["nutriments"]["sodium"]:0),
                    ];
                //}

                    if(!empty($product_array))
                    {

                        if (array_key_exists("image_url", $value))
                        {

                            $product_array['product_image'] = ($value["image_url"]?$value["image_url"]:'');//validateValue($value['image_url'], "");
                        }

                        if (array_key_exists("fat_amount", $value)) {

                            $product_array['fat_amount'] = ($value["nutriments"]["fat_amount"]?$value["nutriments"]["fat_amount"]:'');//$value["nutriments"]["fat_amount"];
                        }

                        $insert_response = addData($connection, '', TABLE_PRODUCT, $product_array);
                            if ($insert_response[STATUS_KEY] == SUCCESS) {
                                $last_inserted_id = $insert_response[MESSAGE_KEY];


                                //******** Insert data into history ********//
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
                                    $message = "Product successfully fetched";
                                }
                            }
                            else
                            {

                                $message = $insert_response[MESSAGE_KEY];
                            }
                    }
                    else
                    {

                        $message = "No Product Available";
                    }

            /*  }
                else
                {

                    $message = "No Product Available";
                }*/

            }
            else
            {
                $message = "No Products Available";
            }
            }
            else
            {

//            $url = "https://world.openfoodfacts.org/api/v0/product/5400141054651.json";
//            $url = "https://world.openfoodfacts.org/api/v0/product/" . urlencode($product_name) . ".json";
//            $url = "https://ssl-api.openfoodfacts.org/code/5400141054651.json";
              $url = "https://ssl-api.openfoodfacts.org/code/".urlencode($product_name).".json";


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
                    $value = $tempArr['products'][0];
                     $product_array = [
                    'product_name' => ($value['product_name']?$value['product_name']:''),
                    'barcode_id' => ($tempArr['code']?$value['code']:''),
                    'is_delete' => ($is_delete?$is_delete:0),
                    'is_test' => ($is_testdata?$is_testdata:0),
                    'ingredients' => ($value['ingredients_text']?$value['ingredients_text']:''),
                    'saturated_fats' => ($value["nutriments"]["saturated-fat"]?$value["nutriments"]["saturated-fat"]:0),
                    'protein' => ($value["nutriments"]["proteins"]?$value["nutriments"]["proteins"]:0),
                    'sugar' => ($value["nutriments"]["sugars"]?$value["nutriments"]["sugars"]:0),
                    'salt' => ($value["nutriments"]["salt"]?$value["nutriments"]["salt"]:0),
                    'carbohydrate' => ($value["nutriments"]["carbohydrates"]?$value["nutriments"]["carbohydrates"]:0),
                    'dietary_fiber' => ($value["nutriments"]["fiber_value"]?$value["nutriments"]["fiber_value"]:0),
                    'sodium' => ($value["nutriments"]["sodium"]?$value["nutriments"]["sodium"]:0),
                    ];

                    if(!empty($product_array))
                    {

                        if (array_key_exists("image_url", $value))
                        {

                            $product_array['product_image'] = ($value["image_url"]?$value["image_url"]:'');//validateValue($value['image_url'], "");
                        }

                        if (array_key_exists("fat_amount", $value)) {

                            $product_array['fat_amount'] = ($value["nutriments"]["fat_amount"]?$value["nutriments"]["fat_amount"]:'');//$value["nutriments"]["fat_amount"];
                        }

                        $insert_response = addData($connection, '', TABLE_PRODUCT, $product_array);
                            if ($insert_response[STATUS_KEY] == SUCCESS) {
                                $last_inserted_id = $insert_response[MESSAGE_KEY];


                                //******** Insert data into history ********//
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
                                    $message = "Product successfully fetched";
                                }
                            }
                            else
                            {
                                $message = $insert_response[MESSAGE_KEY];
                            }
                    }
                    else
                    {

                        $message = "No Product Available";
                    }
            }
            else
            {
                $message = "No Products Available";
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
        $is_delete = DELETE_STATUS::IS_DELETE;
        $edit_history_response = editData($connection, "removeProductFromHistory", TABLE_HISTORY, ['is_delete' => $is_delete], ['id' => $history_id], "");
        if ($edit_history_response[STATUS_KEY] === SUCCESS) {
            $objHistory = getSingleTableData($connection, TABLE_HISTORY, '', '*', '', ['id' => $history_id]);
            if (!empty($objHistory)) {
                $conditional_array = ['product_id' => $objHistory['product_id'], 'user_id' => $objHistory['user_id'], 'is_delete' => $is_delete];
                editData($connection, "addToFavourite", TABLE_FAVOURITE, ['is_favourite' => $is_favourite = '0'], $conditional_array, "");
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
        $is_delete = IS_DELETE;
        $select_user_history_query = "SELECT h.id as history_id, h.user_id, h.product_id, h.created_date AS history_created_date , p.* FROM history AS h
                                      LEFT JOIN product AS p ON p.id = h.product_id
                                      WHERE h.user_id = :user_id AND h.is_delete = :is_delete ORDER BY h.created_date DESC limit $from_index,$to_index ";
        $conditional_array = ['user_id' => $user_id, 'is_delete' => $is_delete];
        $select_user_history_stmt = getMultipleTableData($connection, "", $select_user_history_query, "", "", $conditional_array);
        if ($select_user_history_stmt->rowCount() > 0) {
            while ($history = $select_user_history_stmt->fetch(PDO::FETCH_ASSOC)) {
                //******************* get user favourite ****************//
                $is_favourite = 1;
                $conditional_array = ['product_id' => $history['product_id'], 'user_id' => $user_id, 'is_favourite' => $is_favourite, 'is_delete' => $is_delete];
                $objFavourite = getSingleTableData($connection, TABLE_FAVOURITE, "", "id", "", $conditional_array);
                if (!empty($objFavourite)) {
                    $history['is_favourite'] = 1;
                } else {
                    $history['is_favourite'] = 0;
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

        $is_delete = IS_DELETE;
        $is_favourite = "1";

        $select_user_favourite_query = "SELECT f.id as favourite_id , f.user_id, f.product_id, f.is_favourite, f.created_date AS favourite_created_date , p.* FROM " . TABLE_FAVOURITE . " AS f
                                        LEFT JOIN " . TABLE_PRODUCT . " AS p ON p.id = f.product_id
                                        WHERE f.user_id = :user_id AND f.is_favourite = :is_favourite AND f.is_delete = :is_delete ORDER BY f.created_date DESC limit $from_index,$to_index ";

        $select_user_favourite_stmt = getMultipleTableData($connection, "", $select_user_favourite_query, "", "", ['user_id' => $user_id, 'is_favourite' => $is_favourite, 'is_delete' => $is_delete]);

        if ($select_user_favourite_stmt->rowCount() > 0) {
            while ($product = $select_user_favourite_stmt->fetch(PDO::FETCH_ASSOC)) {
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

        $is_delete = IS_DELETE;
        $current_date = date(DATETIME_FORMAT);

        $conditional_array = ['product_id' => $product_id, 'user_id' => $user_id, 'is_delete' => $is_delete];
        $objFavourite = getSingleTableData($connection, TABLE_FAVOURITE, "", "id,is_favourite", "", $conditional_array);

        if (!empty($objFavourite))
        {
            $edit_response = editData($connection, "addToFavourite", TABLE_FAVOURITE, ['is_favourite' => $is_favourite, 'created_date' => $current_date], ['id' => $objFavourite['id']], "");

            if ($edit_response[STATUS_KEY] === SUCCESS) {
                $status = SUCCESS;
                $message = $is_favourite == 1 ? 'Product added in favourite.' : 'Product remove from favourite.';
            } else {
                $status = FAILED;
                $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            }
        } else {
            $favourite_product_array = ['user_id' => $user_id, 'product_id' => $product_id, 'is_favourite' => $is_favourite, 'created_date' => $current_date];
            $favourite_response = addData($connection, "addToFavourite", TABLE_FAVOURITE, $favourite_product_array);
            if ($favourite_response[STATUS_KEY] == SUCCESS) {
                $status = SUCCESS;
                $message = FAVOURITE_SUCCESSFULLY;
            } else {
                $status = FAILED;
                $message = SOMETHING_WENT_WRONG_TRY_AGAIN_LATER;
            }
        }
        $data['status'] = $status;
        $data['message'] = $message;

        return $data;
    }
}
