<?php

//include 'config.php';   
include 'class.phpmailer.php';



//date_default_timezone_set('Asia/Calcutta');
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: NIPL App' . "\r\n";

$subject = "Test Email"; //'Welcome from NextDoorMenu App';

$mail = new PHPMailer();
$mail->IsSMTP(); // telling the class to use SMTP
//$mail->Host = "mail.yourdomain.com"; // SMTP server
$mail->SMTPDebug = false; // enables SMTP debug information (for testing)
// 1 = errors and messages
// 2 = messages only
$mail->SMTPAuth = true; // enable SMTP authentication
$mail->SMTPSecure = "ssl"; // sets the prefix to the servier
$mail->Host = "smtp.webfaction.com"; // sets GMAIL as the SMTP server
$mail->Port = 465; // set the SMTP port for the GMAIL server

$mail->Username = "lifyzer"; // GMAIL username
$mail->Password = '1784Y3))*ScanF0Odapi$'; // GMAIL password

$from = "hello@lifyzer.com"; //'demo.narola@gmail.com';
$to = "mgu.narola@gmail.com";
$mail->SetFrom($from,"Food scan test");
$mail->Subject = $subject;
//$mail->MsgHTML($content);
$mail->IsHTML(true);
$mail->Body = "This is a test email";


$address = $to;

$mail->AddAddress($address);
if($mail->Send()){
	echo "sucsess";
	return true;
}else {
	echo "failed";
	return false;
}


//===============


/*

class SendEmail
{
    //put your code here
    // constructor
    function __construct()
    {

    }

	function sendEmail($sender_email_id,$message, $mailSubject, $userEmailId,$appName)
	{
		date_default_timezone_set('Asia/Calcutta');
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: NIPL App' . "\r\n";

		$subject = $mailSubject; //'Welcome from NextDoorMenu App';

		$mail = new PHPMailer();
		$mail->IsSMTP(); // telling the class to use SMTP
		//$mail->Host = "mail.yourdomain.com"; // SMTP server
		$mail->SMTPDebug = false; // enables SMTP debug information (for testing)
		// 1 = errors and messages
		// 2 = messages only
		$mail->SMTPAuth = true; // enable SMTP authentication
		$mail->SMTPSecure = "ssl"; // sets the prefix to the servier
		$mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
		$mail->Port = 465; // set the SMTP port for the GMAIL server

	    $mail->Username = SENDER_EMAIL_ID; // GMAIL username
	    $mail->Password = SENDER_EMAIL_PASSWORD; // GMAIL password

		$from = $sender_email_id; //'demo.narola@gmail.com';
		$to = $userEmailId;
		$mail->SetFrom($from, $appName . ' Team');
		$mail->Subject = $subject;
		//$mail->MsgHTML($content);
		$mail->IsHTML(true);
		$mail->Body = $message;


		$address = $to;

		$mail->AddAddress($address);
		if($mail->Send()){
			return true;
		}else{
			return false;
		}
	}
}*/

?>