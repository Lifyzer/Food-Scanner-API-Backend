<?php
// TODO: FILE UNUSED (for the moment)

class GCM
{
    const GOOGLE_API_KEY = 'THE-GOOGLE-API-KEY';

    const IOS_PUSH_NOTIFICATION = 'sendPushIOS';
    const ANDROID_PUSH_NOTIFICATION = 'send_notification';

     public function call_service($service,$deviceToken,$Notifsubject,$pushMessage,$isReject)
     {
        switch($service) {
            case self::IOS_PUSH_NOTIFICATION:
                return $this->sendPushiOS($deviceToken,$Notifsubject,$pushMessage,$isReject);

            case self::ANDROID_PUSH_NOTIFICATION:
                return $this->send_notification($deviceToken, $pushMessage,$Notifsubject,$isReject);
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
	
		if ($development) {
			$apns_url = 'gateway.sandbox.push.apple.com';
			$apns_cert = 'ck_Dev.pem';
           // $apns_url = 'gateway.push.apple.com';
            //$apns_cert = 'ck_Prod.pem';
		} else {
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
            'Authorization: key=' . self::GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            exit('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);
        // echo $result;
    }
}
