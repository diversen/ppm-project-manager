<?php

declare(strict_types=1);

namespace App\Utils;

use Aidantwoods\SecureHeaders\SecureHeaders;

use Pebble\Random;
use Pebble\Service\ConfigService;

trait CSP
{

    private static $nonce;
    public static function getNonce()
    {
        return self::$nonce;
    }

    public function sendCSPHeaders()
    {
        $config = (new ConfigService())->getConfig();

        if (!$config->get("CSP.enabled")) {
            return;
        }

        self::$nonce = Random::generateRandomString(16);

        $nonce = self::$nonce;

        $headers = new SecureHeaders();
        $headers->strictMode(false);
        $headers->errorReporting(true);
        $headers->hsts();
        $headers->csp('default', 'self');
        $headers->csp('base-uri', $config->get('App.server_url'));
        $headers->csp('img-src', 'data:');
        $headers->csp('img-src', $config->get('App.server_url'));
        
        $headers->csp('script-src', "'nonce-$nonce'");

        $headers->csp('script-src', "https://*.googletagmanager.com");
        $headers->csp('img-src', 'https://*.google-analytics.com https://*.googletagmanager.com');
        $headers->csp('connect-src', 'https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com');
        $headers->csp('connect-src', $config->get('App.server_url'));
        $headers->csp('style-src', 'self');
        $headers->csp('style-src', 'https://cdnjs.cloudflare.com');
        $headers->csp('font-src', 'https://cdnjs.cloudflare.com');

        $headers->csp('worker-src', $config->get('App.server_url'));
        $headers->apply();
    }


}
