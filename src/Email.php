<?php

namespace Lifyzer\Api;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    private const SMTP_HOST_SERVER = 'smtp.gmail.com'; //sets GMAIL as the SMTP server
    private const SMTP_PORT_SERVER = 465; // set the SMTP port for the GMAIL server

    /**
     * @param string $sender_email_id
     * @param string $message
     * @param string $subject
     * @param string $userEmailId
     *
     * @return bool
     * @throws Exception
     */
    public function sendMail(string $sender_email_id, string $message, string $subject, string $userEmailId): bool
    {
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: NIPL App' . "\r\n";

        $mail = new PHPMailer();
        $mail->isSMTP(); // telling the class to use SMTP
        $mail->SMTPDebug = DEBUG_MODE; // enables SMTP debug information (for testing)

        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
        $mail->host = self::SMTP_HOST_SERVER;
        $mail->port = self::SMTP_PORT_SERVER;

        $mail->username = SENDER_EMAIL_ID; // GMAIL username
        $mail->password = SENDER_EMAIL_PASSWORD; // GMAIL password

        $mail->setFrom($sender_email_id, APPNAME . 'Team');
        $mail->subject = $subject;

        $mail->isHTML(false);
        $mail->Body = $message;

        $mail->addAddress($userEmailId);

        return $mail->send();
    }
}
