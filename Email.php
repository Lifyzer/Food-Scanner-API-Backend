<?php

namespace Lifyzer\Api;

use PHPMailer;

require 'class.phpmailer.php';

class SendEmail
{
    public function sendEmail($sender_email_id, $message, $Mailsubject, $userEmailId)
    {
        date_default_timezone_set('Asia/Calcutta');
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: NIPL App' . "\r\n";

        $subject = $Mailsubject; //'Welcome from NextDoorMenu App';

        $mail = new PHPMailer();
        $mail->IsSMTP(); // telling the class to use SMTP
        //$mail->Host = "mail.yourdomain.com"; // SMTP server
        $mail->SMTPDebug = false; // enables SMTP debug information (for testing)
        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
        //$mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
        $mail->Host = "smtp.1and1.com"; // sets GMAIL as the SMTP server
        $mail->Port = 465; // set the SMTP port for the GMAIL server

        $mail->Username = SENDER_EMAIL_ID; // GMAIL username
        $mail->Password = SENDER_EMAIL_PASSWORD; // GMAIL password

        $from = $sender_email_id; //'demo.narola@gmail.com';
        $to = $userEmailId;
        $mail->SetFrom($from, APPNAME . 'Team');
        $mail->Subject = $subject;
        //$mail->MsgHTML($content);
        $mail->IsHTML(true);
        $mail->Body = $message;

        /*$mail->Body = '<html>
        <body style=\"font-family:Arial; font-size:12px; color:#666666;\">
          "'.$message.'"
        </body>
      </html>';*/

        $address = $to;

        $mail->AddAddress($address);
        $mail->Send();

        //echo "EMAIL SENDED YOOOO!!!";
    }
}
