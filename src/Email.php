<?php

namespace Lifyzer\Api;

//require_once __DIR__ . '/vendor/autoload.php'; <= This one is already included in /FoodScanAppService.php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Email
{
    private const SMTP_HOST_SERVER = 'smtp.webfaction.com';
    private const SMTP_PORT_SERVER = 465;
    private const SMTP_PREFIX_SERVER = 'ssl';

    /** @var PHPMailer */
    private $oMail;

    public function __construct()
    {
        $this->oMail = new PHPMailer();
    }

    /**
     * @param string $message
     * @param string $subject
     * @param string $userEmailId
     *
     * @return bool
     *
     * @throws Exception
     */
    public function sendMail(string $message, string $subject, string $userEmailId): bool
    {
        $sName = getenv('NAME');
        $senderEmailId = getenv('SENDER_EMAIL_ID');
        $senderEmailPassword = getenv('SENDER_EMAIL_PASSWORD');

        // Server settings
        $this->oMail->isSMTP();
        $this->oMail->CharSet = PHPMailer::CHARSET_UTF8;
        $this->oMail->SMTPSecure = self::SMTP_PREFIX_SERVER;
        $this->oMail->SMTPAuth = true;
        $this->oMail->Port = self::SMTP_PORT_SERVER;
        $this->oMail->Host = self::SMTP_HOST_SERVER;
        $this->oMail->Username = $senderEmailId;
        $this->oMail->Password = $senderEmailPassword;
        $this->oMail->SMTPDebug = SMTP::DEBUG_SERVER; // When need to show log of SMTP process

        // Recipients
        $this->oMail->setFrom($senderEmailId, $sName);
        $this->oMail->addAddress($userEmailId);

        // Content
        $this->oMail->isHTML(true);
        $this->oMail->Subject = $subject;
        $this->oMail->Body = $message;

        return (bool)$this->oMail->send();
    }
}
