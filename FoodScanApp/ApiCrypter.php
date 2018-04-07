<?php
/**
 * Created by PhpStorm.
 * User: c157
 * Date: 17/01/18
 * Time: 12:22 PM
 */


class Security {
     public static function encrypt($input, $key) {

         $iv = "@#$%!@#$#$%!@#$%"; //"@@@@&&&&$$$$%%%%"; //you can change it.
         $data = openssl_encrypt ( $input , "AES-128-CBC" , $key, 0, $iv );
         return $data;
     }

    public static function decrypt($crypt, $sKey) {

        $iv = "@#$%!@#$#$%!@#$%";
        $data = openssl_decrypt ( $crypt , "AES-128-CBC" , $sKey, 0, $iv );
        return $data;

    }
}
