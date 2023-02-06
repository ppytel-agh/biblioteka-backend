<?php

$key = bin2hex(random_bytes(6));

$iv = bin2hex(random_bytes(8));

$methods = openssl_get_cipher_methods();
//var_dump($methods);

$cipher = 'aes-128-ctr';

$data = $argv[1];

$encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);

echo "\$iv = '";
echo $iv;
echo "';\n\$key = '";
echo $key;
echo "';\n\$encrypted = '";
echo $encrypted;
echo "';\n";