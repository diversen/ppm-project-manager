<?php

// If not a server then don't include these settings
if (!isset($_SERVER['SERVER_NAME'])) {
    return [];
}

return
[
    'cookie_days' => 365,
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_domain' => $_SERVER['SERVER_NAME'],
    'cookie_http' => true
];
