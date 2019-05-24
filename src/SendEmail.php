<?php

//include 'config.php';   
include 'class.phpmailer.php';
//include 'ConstantValues.php';

class SendEmail
{
    //put your code here
    // constructor
    function __construct()
    {
		//echo "dbhs";
    }

    function sendemail($sender_email_id,$message, $Mailsubject, $userEmailId)
    {
       // date_default_timezone_set('Asia/Calcutta');
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";   //charset=iso-8859-1
        $headers .= 'From: Lifyzer App' . "\r\n";
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth= true;
        $mail->Port = 465; // Or 587
        $mail->Username = "lifyzer"; //SENDER_EMAIL_ID; // GMAIL username
        $mail->Password = '1784Y3))*ScanF0Odapi$'; //SENDER_EMAIL_PASSWORD; // GMAIL password
        $mail->SMTPSecure = 'ssl';
        $mail->From = "hello@lifyzer.com"; //SENDER_EMAIL_ID;
        $mail->FromName= APPNAME;
        $mail->isHTML(true);
        $mail->Subject = $Mailsubject;
        $mail->Body = $message;
        $mail->addAddress($userEmailId);
        if ($mail->send()) {
            //success
        } else {
            $post['message'] = $mail->ErrorInfo;
            $post['status'] = 0;
            return 0;
        }
    }
}

?>

