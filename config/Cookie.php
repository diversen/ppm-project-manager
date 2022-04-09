<?php

// Default cookie settings
return
[
    // How long will the cookie last when 'keep me signed in'
    'cookie_seconds' => 365 * 24 * 60 * 60, // 365 days

    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_domain' => $_SERVER['SERVER_NAME'] ?? '',
    'cookie_http' => true
];
