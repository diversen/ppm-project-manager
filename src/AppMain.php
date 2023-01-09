<?php

declare(strict_types=1);

namespace App;

use Pebble\Router;
use Pebble\App\AppBase;
use App\Settings\SetupIntl;

/**
 * App class. This is the main class for the app.
 * It is used in www/index.php
 */
class AppMain extends AppBase
{
    use \App\Utils\CSP;

    public const VERSION = "v2.1.2-beta-30";

    public function run()
    {

        // Add '.' and 'src' to include path
        $this->addBaseToIncudePath();
        $this->addSrcToIncludePath();
        $this->setErrorHandler();
        $this->sendSSLHeaders();
        $this->sendCSPHeaders();
        $this->sessionStart();
        $this->setDebug();

        (new SetupIntl())->setupIntl();
        $router = new Router();

        $router->setFasterRouter();
        $router->addClass(\App\Test\Controller::class);
        $router->addClass(\App\Info\Controller::class);
        $router->addClass(\App\Admin\Controller::class);
        $router->addClass(\App\Notification\Controller::class);
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
