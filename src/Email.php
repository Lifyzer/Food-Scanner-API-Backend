<?php

namespace Lifyzer\Api;

use PHPMailer;

require 'class.phpmailer.php'; 
include 'class.smtp.php';

class Email
{
   
    public function sendMail(string $sender_email_id, string $message, string $subject, string $userEmailId): bool
    {

        echo "Enterded";

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: NIPL App' . "\r\n";

        $mail = new PHPMailer();
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->SMTPDebug = false;
        //$mail->Host = "mail.yourdomain.com"; // SMTP server
       // $mail->SMTPDebug = false; // enables SMTP debug information (for testing)
        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
        //$mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
        $mail->Host = "smtp.gmail.com";//"smtp.1and1.com"; // sets GMAIL as the SMTP server
        $mail->Port = 465; // set the SMTP port for the GMAIL server

        $mail->Username = SENDER_EMAIL_ID; // GMAIL username
        $mail->Password = SENDER_EMAIL_PASSWORD; // GMAIL password

        $mail->SetFrom($sender_email_id, APPNAME . 'Team');
        $mail->Subject = $subject;

        echo $sender_email_id,$message,$subject,$userEmailId;
        //$mail->MsgHTML($content);
        $mail->IsHTML(false);
        $mail->Body = $message;

        $mail->AddAddress($userEmailId);
        return $mail->Send();
    }
}


