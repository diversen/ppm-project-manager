<?php

use Aidantwoods\SecureHeaders\SecureHeaders;
use Pebble\Random;

$nonce = Random::generateRandomString(16);

$headers = new SecureHeaders();
$headers->strictMode(false);
$headers->errorReporting(true);
$headers->hsts();
$headers->csp('default', 'self');
$headers->csp('base-uri', 'self');
$headers->csp('img-src', 'data:');
$headers->csp('img-src', 'self');
$headers->csp('script-src', "'nonce-$nonce'");
$headers->csp('script-src', "https://*.googletagmanager.com");
$headers->csp('img-src', 'https://*.google-analytics.com https://*.googletagmanager.com');
$headers->csp('connect-src', 'https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com');
$headers->csp('connect-src', 'self');
$headers->csp('style-src', 'self');
$headers->csp('style-src', 'https://cdnjs.cloudflare.com');
$headers->csp('font-src', 'https://cdnjs.cloudflare.com');
$headers->csp('worker-src', 'self');

return [
    'enabled' => true,
    'headers' => $headers,
    'nonce' => $nonce,
];
