<?php
include 'class.smtp.php';

class SendEmailFunction
{
	function __construct()
	{

		
	}

//Send Via Gmail
//	function sendEmail($body,$toEmail,$subject)
//	{
//		require_once('class.phpmailer.php');
//		$headers = 'MIME-Version: 1.0' . "\r\n";
//		$headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";
//		$headers .= 'From: facetag' . "\r\n";
//		$mail = new PHPMailer();
//		$mail->IsSMTP();
//		$mail->SMTPAuth = true;
//		$mail->SMTPSecure = "ssl";
//		$mail->Host = "smtp.gmail.com";
//		$mail->Port = 465;
//		$mail->Username = "demo.narolainfotech@gmail.com";
//		$mail->Password = "password123#";
//		//$mail->SetFrom('demo.narolainfotech@gmail.com', 'facetag');
//		$mail->SetFrom('verify@facetag.com.au', 'facetag');
//		$mail->Subject = $subject;
//		$mail->IsHTML(true);
//		$mail->Body = $body;
//		$mail->AddAddress($toEmail);
//		$mail->Send();
//	}



	function sendEmail($body,$toEmail,$subject)
	{
	echo '12';
		error_reporting(E_ALL);
ini_set('display_errors', 1);
	//echo "Enters",SENDER_EMAIL_ID,SENDER_EMAIL_PASSWORD;
		date_default_timezone_set('Asia/Calcutta');
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";   //charset=iso-8859-1
		$headers .= 'From: ParTAG App' . "\r\n";
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->CharSet = 'UTF-8';
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth= true;
		$mail->SMTPDebug= true;
		$mail->Port = 465; // Or 587
		$mail->Username = SENDER_EMAIL_ID; // GMAIL username
		$mail->Password = SENDER_EMAIL_PASSWORD; // GMAIL password
		$mail->SMTPSecure = 'ssl';
		$mail->From = SENDER_EMAIL_ID;
		$mail->FromName= APPNAME;
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->addAddress($toEmail);
		$temp = $mail->send();
		echo '<pre>';
		print_r($temp);
		echo '</pre>';
//		if ($mail->send()) {
//			echo"y";
//			return 1;
//		} else {
//			$post['message'] = $mail->ErrorInfo;
//			$post['status'] = 0;
//			echo"n";
//			return 0;
//		}
	}
}

?>