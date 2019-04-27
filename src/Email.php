<?php

declare(strict_types=1);

namespace Lifyzer\Api;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    /**
     * @param string $sender_email_id
     * @param string $message
     * @param string $subject
     * @param string $userEmailId
     *
     * @return bool
     * @throws Exception
     */
    public function send(string $sender_email_id, string $message, string $subject, string $userEmailId): bool
    {
        $mail = new PHPMailer();
        $mail->isSMTP(); // telling the class to use SMTP
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

        $mail->setFrom($sender_email_id, APPNAME . 'Team');
        $mail->Subject = $subject;
        //$mail->msgHTML($content);
        $mail->isHTML(true);
        $mail->Body = $message;

        $mail->addAddress($userEmailId);

        return $mail->send();
    }
}
