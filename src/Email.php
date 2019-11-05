<?php

namespace Lifyzer\Api;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    private const SMTP_HOST_SERVER = 'smtp.webfaction.com';
    private const SMTP_PORT_SERVER = 465;

    /**
     * @param string $message
     * @param string $subject
     * @param string $userEmailId
     *
     * @return bool
     * @throws Exception
     */
    public function sendMail(string $message, string $subject, string $userEmailId): bool
    {
        $senderEmailId = getenv('SENDER_EMAIL_ID') !== false ? getenv('SENDER_EMAIL_ID') : SENDER_EMAIL_ID;
        $senderEmailPassword = getenv('SENDER_EMAIL_PASSWORD') !== false ? getenv('SENDER_EMAIL_PASSWORD') : SENDER_EMAIL_PASSWORD;

        $mail = new PHPMailer();
        $mail->isSMTP(); // telling the class to use SMTP
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->SMTPSecure = 'ssl'; // sets the prefix to the server
        $mail->Host = self::SMTP_HOST_SERVER;
        $mail->Port = self::SMTP_PORT_SERVER;
        $mail->Username = $senderEmailId;
        $mail->Password = $senderEmailPassword;
        $mail->setFrom($senderEmailId, APPNAME);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->addAddress($userEmailId);

        return (bool)$mail->send();
    }
}
