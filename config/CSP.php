<?php

// Usage Example
$nonce = bin2hex(random_bytes(16));

$config = [
    // Enabled
    'enabled' => true,
    
    // The nonce
    'nonce' => $nonce,

    // The CSP headers
    'headers' =>  [
        'default-src' => "'self'",
        'base-uri' => "'self'",
        'img-src' => ["'self'", "data:", "https://*.google-analytics.com", "https://*.googletagmanager.com"],
        'script-src' => ["'nonce-{$nonce}'", "https://*.googletagmanager.com"],
        'connect-src' => ["'self'", "https://*.google-analytics.com", "https://*.analytics.google.com", "https://*.googletagmanager.com"],
        'style-src' => ["'self'", "https://cdnjs.cloudflare.com"],
        'font-src' => ["https://cdnjs.cloudflare.com"],
        'worker-src' => "'self'",
    ]
];

return $config;
