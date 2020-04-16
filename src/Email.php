<?php

namespace Lifyzer\Api;

//require_once __DIR__ . '/vendor/autoload.php'; <= This one is already included in /FoodScanAppService.php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    private const SMTP_HOST_SERVER = 'smtp.webfaction.com';
    private const SMTP_PORT_SERVER = 465;
    private const SMTP_PREFIX_SERVER = 'ssl';

    /**
     * @param string $message
     * @param string $subject
     * @param string $userEmailId
     * @param bool $attachCookbook
     *
     * @return bool
     *
     * @throws Exception
     */
    public function sendMail(string $message, string $subject, string $userEmailId): bool
    {
            $senderEmailId = getenv('SENDER_EMAIL_ID');
            $senderEmailPassword = getenv('SENDER_EMAIL_PASSWORD');

            //Server settings
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->SMTPSecure = self::SMTP_PREFIX_SERVER;
            $mail->SMTPAuth = true;
            $mail->Port = self::SMTP_PORT_SERVER;
            $mail->Host = self::SMTP_HOST_SERVER;
            $mail->Username = "lifyzer";
            $mail->Password = $senderEmailPassword;
            //$mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER; // When need to show log of SMTP process

            //Recipients
            $mail->setFrom($senderEmailId, "Lifyzer");
            $mail->addAddress($userEmailId);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            return (bool)$mail->send();
    }
}
