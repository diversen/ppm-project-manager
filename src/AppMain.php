<?php

declare(strict_types=1);

namespace App;

use Pebble\Router;
use Aidantwoods\SecureHeaders\SecureHeaders;
use App\AppBase;

/**
 * AppMain contains the application logic.
 * It initializes the application logic and runs it.
 */
class AppMain extends AppBase
{

    public function sendHeaders()
    {
        $config = $this->getConfig();
        parent::sendHeaders();
        if ($config->get('App.env') !== 'dev') {
            $headers = new SecureHeaders();
            $headers->hsts();
            $headers->csp('default', 'self');
            $headers->csp('img-src', 'data:');
            $headers->csp('img-src', $config->get('App.server_url'));
            $headers->csp('script', 'unsafe-inline');
            $headers->csp('script-src', $config->get('App.server_url'));
            $headers->csp('default-src', 'unsafe-inline');
            $headers->apply();
        }
    }


    public function run()
    {
        // Define all routes
        $this->setIncludePath();
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
