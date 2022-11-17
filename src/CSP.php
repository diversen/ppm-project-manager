<?php

declare(strict_types=1);

namespace App;

use Aidantwoods\SecureHeaders\SecureHeaders;

use Pebble\Random;
use Pebble\Service\ConfigService;

trait CSP
{
    public static $nonce;

    public function sendCSPHeaders()
    {
        $config = (new ConfigService())->getConfig();

        $env = $config->get("App.env");
        if ($env === 'dev') {
            return;
        }

        self::$nonce = $nonce = Random::generateRandomString(16);

        $headers = new SecureHeaders();
        $headers->strictMode(false);
        $headers->errorReporting(true);
        $headers->hsts();
        $headers->csp('default', 'self');
        $headers->csp('base-uri', $config->get('App.server_url'));
        $headers->csp('img-src', 'data:');
        $headers->csp('img-src', $config->get('App.server_url'));
        $headers->csp('script-src', "'nonce-$nonce'");
        $headers->csp('style-src', 'self');
        $headers->csp('style-src', 'https://cdnjs.cloudflare.com');
        $headers->csp('font-src', 'https://cdnjs.cloudflare.com');

        $headers->csp('worker-src', $config->get('App.server_url'));
        $headers->apply();
    }

    public static function getNonce()
    {
        return self::$nonce;
    }
}
