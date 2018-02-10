<?php
//session_start();
class GCM
{
    function __construct() 
    {
  	  
    }
     public function call_service($service,$deviceToken,$Notifsubject,$pushMessage,$isReject)
    {
    	
        switch($service)
        {
            case "sendPushIOS":
            {
                return $this->sendPushiOS($deviceToken,$Notifsubject,$pushMessage,$isReject);
            }
                break;
            case "send_notification":
            {
                return $this->send_notification($deviceToken, $pushMessage,$Notifsubject,$isReject);
            }
        }
    }


    public function sendPushiOS($deviceToken,$Notifsubject,$pushMessage,$isReject)
    {
		$development=true;
		$message=$pushMessage;
		$badge=0;
		$sound='default';
		$passphrase = 'password';



		$payload = array();
        if($isReject == 0)
        {
            $payload['aps'] = array('alert' => $message, 'badge' => intval($badge), 'sound' => $sound);
        }
        else
        {
            $payload['aps'] = array('alert' => "", 'badge' => intval($badge), 'sound' => "",'content-available' => 1 );
            //print_r($payload['aps']);
            //exit;
        }


		$payload['custom'] = $message;
		$payload = json_encode($payload);
	
		$apns_url = NULL;
		$apns_cert = NULL;
		$apns_port = 2195;
	
		if($development)
		{
			$apns_url = 'gateway.sandbox.push.apple.com';
			$apns_cert = 'ck_Dev.pem';
           // $apns_url = 'gateway.push.apple.com';
            //$apns_cert = 'ck_Prod.pem';
		}
		else
		{
			$apns_url = 'gateway.push.apple.com';
			$apns_cert = 'ck_Prod.pem';
		}
	
		$stream_context = stream_context_create();
		stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);
		stream_context_set_option($stream_context, 'ssl', 'passphrase', $passphrase);
	
		$apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 300, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $stream_context);
		$status=2;
		if($error) {
			//print("\nAPN: Maybe some errors: $error: $error_string");
			$status=2;
		}
	
		if (!$apns) {
	
			if ($error) {
			   // print("\nAPN Failed". 'ssl://' . $apns_url . ':' . $apns_port. " to connect: $error $error_string");
			   $status=2;
			}
			else {
			   // print("\nAPN Failed to connect: Something wrong with context");
			   $status=2;
			}
		}
		else {
		   // print("\nAPN: Opening connection to: {ssl://" . $apns_url . ":" . $apns_port. "}");
			
				foreach($deviceToken as $device_token)
				{
					$apns_message = chr(1)
						. pack("N", time())
						. pack("N", time() + 30000)
						. pack('n', 32)
						. pack('H*', str_replace(' ', '', $device_token))
						. pack('n', strlen($payload))
						. $payload;
					$result = fwrite($apns, $apns_message, strlen($apns_message));
	
					if($result) {
						//echo "sent";
						$status=1;
					}
					else {
					   // echo "not sent";
					   $status=2;
					}
				}
			fclose($apns);
		}
		return $status;
    }

    public function send_notification($registatoin_ids, $message,$key,$isReject) {
        // include config
        //include_once './config.php';

        //   $GOOGLE_API_KEY = "AIzaSyABiZeJp_4W4P8mLr9YIEHsPbObdXFe6nw";
        //$GOOGLE_API_KEY = "AIzaSyA0wqkE5CHK-peZbqi2lzdAAvyo3Kb8qQw";
        $GOOGLE_API_KEY = "AIzaSyBrj8QuOdJ6i6uNmMkze2z1qwmhEFq027I";

        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';

        $data['message']=$message;
        $data['isReject']=$isReject;
        $data['key']=$key;
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $data,
        );

        $headers = array(
            'Authorization: key=' . $GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);
        // echo $result;
    }
}
?>