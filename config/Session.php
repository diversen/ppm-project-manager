<?php

// One year
$lifetime = 60 * 60 * 24 * 365;
return [
    'lifetime' => $lifetime,
    'path' => '/',
    // prefix with a dot to use all domains e.g. .php.net
    'domain' => $_SERVER['SERVER_NAME'] ?? '',
    'secure' => false,
    'httponly' => true,
];
