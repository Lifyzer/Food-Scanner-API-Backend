<?php
/**
 * Created by PhpStorm.
 * User: c63
 * Date: 07/05/16
 * Time: 2:52 PM
 */

//require_once('class.phpmailer.php');


class AppUtilities 
{
    function __construct()
    {

    }
    public function call_service($service, $postData)
    {
        switch($service)
        {
            case "VersionStatus":
            {
                return $this->getVersionStatus($postData);
            }
                break;
            case "UpdateUserVersion":
            {
                return $this->updateUserVersion($postData);
            }
                break;  
            case "SendPushForAppUpdate":
            {
                return $this->sendPushForAppUpdate($postData);
            }
                break;           
        }
    }

    public function getVersionStatus($postData)
    {
    	$connection = $GLOBALS['con'];
        $posts = array();

        $version = validateObject ($postData , 'current_version', "");
        $version = addslashes($version);

        $platform = validateObject ($postData , 'platform', "");
        $platform = addslashes($platform);

        $isTestData = validateObject ($postData , 'is_testdata', 1);
        $isTestData = addslashes($isTestData);


        $message = "";
        $select_query = "Select version_no, platform, api_update_status, update_by_date from " . TABLE_VERSION_LOG . " where version_no = '".$version." ' AND platform = '".$platform."'";
        $result = mysqli_query($connection,$select_query) or $errorMsg =  mysqli_error($connection);

        if ((mysqli_num_rows($result)) > 0 )
        {
            $status = 1;
            while ($fetchedVersion = mysqli_fetch_assoc($result))
            {
                 $message = "api update status is fetched";                      
  				 $posts	= $fetchedVersion;     
            }
        }
        else
        {
            $status = 2;
            $errorMsg = 'Sorry, version not found.';
            $posts = null;
        }

        $data['status'] = ($status > 1) ? FAILED : SUCCESS;
        $data['message'] = $errorMsg;
        $data['data'] = $posts;
        return $data;
    }
	/*public function updateUserVersion($postData)
    {
    	$connection = $GLOBALS['con'];
        $posts = array();

        $version = validateObject ($postData , 'current_version', "");
        $version = addslashes($version);

        $platform = validateObject ($postData , 'platform', "");
        $platform = addslashes($platform);

        $user_id = validateObject ($postData , 'user_id', "");
        $user_id = addslashes($user_id);

		$config_key = "";
		if(strcmp($platform, "iOS"))
		{
		  $config_key = "iOSLatestVersion";
		}
		else if($platform == "Android")
		{
		  $config_key = "AndroidLatestVersion"; 
		}

        $message = "";
        //$select_query = "Select config_key, config_value from " . TABLE_ADMIN_CONFIG . " where config_key = '".$config_key."'";
        $select_query = "Select config_key, config_value from " . TABLE_USER . " where config_key = '".$config_key."'";
        $result = mysqli_query($connection,$select_query) or $errorMsg =  mysqli_error($connection);
        if ((mysqli_num_rows($result)) > 0 )
        {
            $status = 2;
            while ($fetchedVersion = mysqli_fetch_assoc($result))
            {
                 if($fetchedVersion['config_value'] != $version)
                 {	
			        $update_query = "Update ". TABLE_USER ." set api_version = '".$version."' where id = '".$user_id."' ";
			        $update_response = mysqli_query($connection,$update_query) or $error =  mysqli_error($connection);
			        if  ($update_response)
			        {
			        	$message = "Record updated successfully";
			        } 
                 }
            }
        }
        else
        {
            $status = 2;
            $message = 'Sorry, record not found.';
            $posts = null;
        }

        $data['status'] = ($status > 1) ? FAILED : SUCCESS;
        $data['message'] = $message;
        $data['data'] = $posts;
        return $data;
    }*/
    public function generate_array()
    {
 	   $a_params = array();
 
		$param_type = '';
		$n = count($a_param_type);
		for($i = 0; $i < $n; $i++) 
		{
		  $param_type .= $a_param_type[$i];
		}
 
		/* with call_user_func_array, array params must be passed by reference */
		$a_params[] = & $param_type;
 
		for($i = 0; $i < $n; $i++) 
		{
		  /* with call_user_func_array, array params must be passed by reference */
		  $a_params[] = & $a_bind_params[$i];
		}
    }
    
    public function updateUserVersion($postData)
    {    
    	$connection = $GLOBALS['con'];
        $posts = array();

        $version = validateObject ($postData , 'current_version', "");
        $version = addslashes($version);

        $platform = validateObject ($postData , 'platform', "");
        $platform = addslashes($platform);

        $user_id = validateObject ($postData , 'user_id', "");
        $user_id = addslashes($user_id);

		$config_key = "";
		if(strcmp($platform, "iOS"))
		{
		  $config_key = "iOSLatestVersion";
		}
		else if($platform == "Android")
		{
		  $config_key = "AndroidLatestVersion"; 
		}

        $message = "";
        //$select_query = "Select config_key, config_value from " . TABLE_ADMIN_CONFIG . " where config_key = '".$config_key."'";
        $select_query = "Select config_key, config_value from " . TABLE_USER . " where config_key = '".$config_key."'";
         $result = mysqli_query($connection,$select_query) or $errorMsg =  mysqli_error($connection);
          $status = 2;
            while ($fetchedVersion = mysqli_fetch_assoc($result))
            {
                 if($fetchedVersion['config_value'] != $version)
                 {	
			        $update_query = "Update ". TABLE_USER ." set api_version = '".$version."' where id = '".$user_id."' ";
			        $update_response = mysqli_query($connection,$update_query) or $error =  mysqli_error($connection);
			        if  ($update_response)
			        {
			        	$message = "Record updated successfully";
			        } 
                 }
            }
        $data['status'] = ($status > 1) ? FAILED : SUCCESS;
        $data['message'] = $message;
        $data['data'] = $posts;
        return $data;
    }
}

?>