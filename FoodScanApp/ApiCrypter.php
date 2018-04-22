<?php

include_once 'ConstantValues.php';

class Security
{
    public static function encrypt($input, $key) {
        $iv = ENCRYPTION_KEY_IV;
        $plaintext = $input;
        $password = $key;
        $method = 'aes-256-cbc';
        // Must be exact 32 chars (256 bit)
        $password = substr(hash('sha256', $password, true), 0, 32);
        $data = base64_encode(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));
        return $data;
    }

    public static function decrypt($crypt, $sKey) {

        $iv = ENCRYPTION_KEY_IV;
        $method = 'aes-256-cbc';
        $password = $sKey;
        $password = substr(hash('sha256', $password, true), 0, 32);
        $decrypted = openssl_decrypt(base64_decode($crypt), $method, $password, OPENSSL_RAW_DATA, $iv);
        return $decrypted;

    }

    protected function hex2bin($hexdata)
    {
        $bindata = '';
        for ($i = 0, $hexdataSize = strlen($hexdata); $i < $hexdataSize; $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }
}
