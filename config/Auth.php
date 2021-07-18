<?php

return
[
    // How long will the cookie last when 'keep me signed in'
    'cookie_seconds_permanent' => 365 * 24 * 60 * 60, // 365 days
    // Session time when not 'keep me signed in' e.g. 1 hour 60 * 60
    // Set to 0 if it need to be a session cookie. (Notice: They expire unpredictably from the user's perspective)
    'cookie_seconds' => 0,
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_domain' => $_SERVER['SERVER_NAME'] ?? '',
    'cookie_http' => true
];
