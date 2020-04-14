<?php

namespace Lifyzer\Api;
// Load Composer's autoloader
include __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Email
{
    public const ATTACH_COOKBOOK_FILE = true;
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
    public function sendMail(string $message, string $subject, string $userEmailId, bool $attachCookbook = false): bool
    {
            $senderEmailId = getenv('SENDER_EMAIL_ID') !== false ? getenv('SENDER_EMAIL_ID') : SENDER_EMAIL_ID;
            $senderEmailPassword = getenv('SENDER_EMAIL_PASSWORD') !== false ? getenv('SENDER_EMAIL_PASSWORD') : SENDER_EMAIL_PASSWORD;

            //Server settings
            $mail = new PHPMailer();
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
//        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host = self::SMTP_HOST_SERVER;
            $mail->SMTPAuth = true;
            $mail->Username = "lifyzer";
            $mail->Password = $senderEmailPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = self::SMTP_PORT_SERVER;

            //Recipients
            $mail->setFrom($senderEmailId, "Lifyzer");
            $mail->addAddress($userEmailId);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            if ($attachCookbook) {
                $mail->addAttachment(
                    ASSETS_PATH .'/9-Recipe-Vegetarian-Menu.epub',
                    '9 Recipe Vegetarian Cookbook'
                );
            }
            return (bool)$mail->send();
    }
}
