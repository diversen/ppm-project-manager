<?php

declare(strict_types=1);

namespace App;

use Pebble\Router;
use Pebble\Random;
use Aidantwoods\SecureHeaders\SecureHeaders;
use App\Main\AppBase;

/**
 * AppMain contains the application logic.
 * It initializes the application logic and runs it.
 */
class AppMain extends AppBase
{
    public const VERSION = "1.2.109";
    public static $nonce;
    public function sendHeaders()
    {
        $config = $this->getConfig();
        parent::sendHeaders();

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


    public function run()
    {
        $this->setIncludePath(__DIR__);
        $this->setErrorHandler();
        $this->sendHeaders();
        $this->sessionStart();
        $this->setupIntl();
        $this->setDebug();



        $router = new Router();
        $router->addClass(\App\Test\Controller::class);
        $router->addClass(\App\Account\ControllerExt::class);
        $router->addClass(\App\Home\Controller::class);
        $router->addClass(\App\Google\Controller::class);
        $router->addClass(\App\Overview\Controller::class);
        $router->addClass(\App\Project\Controller::class);
        $router->addClass(\App\Settings\Controller::class);
        $router->addClass(\App\Task\Controller::class);
        $router->addClass(\App\Time\Controller::class);
        $router->addClass(\App\Error\Controller::class);
        $router->addClass(\App\TwoFactor\Controller::class);
        $router->run();
    }
}
