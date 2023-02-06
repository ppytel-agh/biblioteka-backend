<?php

namespace App\Database;

class Ciphering
{

    public function __construct()
    {
        $this->cipher = 'aes-128-ctr';
        $this->iv = '78aa6ddbf464e383';
        $this->key = '25576b54aa86';
    }

    public function encrypt($toEncrypt) {
        $encrypted = openssl_encrypt($toEncrypt, $this->cipher, $this->key, 0, $this->iv);
        return $encrypted;
    }

    public function decrypt($encrypted) {
        $decrypted = openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $this->iv);
        return $decrypted;
    }
}