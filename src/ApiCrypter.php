<?php

declare(strict_types=1);

namespace Lifyzer\Api;

class ApiCrypter
{
    public function encrypt($input, $key): string
    {
        $iv = $_ENV['ENCRYPTION_KEY_IV'];
        $plaintext = $input;
        $password = $key;
        $method = 'aes-256-cbc';
        // Must be exact 32 chars (256 bit)
        $password = substr(hash('sha256', $password, true), 0, 32);
        $data = base64_encode(
            openssl_encrypt(
                $plaintext,
                $method,
                $password,
                OPENSSL_RAW_DATA,
                $iv
            )
        );

        return $data;
    }

    public function decrypt($crypt, $sKey): string
    {
        $iv = $_ENV['ENCRYPTION_KEY_IV'];
        $method = 'aes-256-cbc';
        $password = $sKey;
        $password = substr(hash('sha256', $password, true), 0, 32);
        $decrypted = openssl_decrypt(
            base64_decode($crypt),
            $method,
            $password,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted;
    }
}
