<?php

namespace Lifyzer\Api;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    private const SMTP_HOST_SERVER = 'smtp.webfaction.com'; //sets GMAIL as the SMTP server
    private const SMTP_PORT_SERVER = 465; // set the SMTP port for the GMAIL server

    /**
     * @param string $senderEmailId
     * @param string $message
     * @param string $subject
     * @param string $userEmailId
     *
     * @return bool
     * @throws Exception
     */
    public function sendMail(string $senderEmailId, string $message, string $subject, string $userEmailId): bool
    {
        $mail = new PHPMailer();
        $mail->isSMTP(); // telling the class to use SMTP
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->SMTPSecure = 'ssl'; // sets the prefix to the server
        $mail->Host = self::SMTP_HOST_SERVER;
        $mail->Port = self::SMTP_PORT_SERVER;
        $mail->Username = getenv('SENDER_EMAIL_ID');
        $mail->Password = getenv('SENDER_EMAIL_PASSWORD');
        $mail->setFrom($senderEmailId, APPNAME);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->addAddress($userEmailId);

        return $mail->send();
    }
}
