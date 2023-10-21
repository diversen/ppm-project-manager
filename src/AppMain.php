<?php

/**
 * AppMain class. This is the main class for the app.
 * It is used in the entry point www/index.php
 */

declare(strict_types=1);

namespace App;

use Pebble\Router;
use App\Settings\SetupIntl;
use App\AppUtils;


class AppMain extends AppUtils
{
    public const VERSION = "v3.0.1";
    public function __construct()
    {
    }

    public function run()
    {
        // Set up include_path and some other stuff before we construct all services
        // Doing this here means we will catch all errors in a nice way
        $utils = $this->getUtils();
        $utils->addBaseToIncudePath();
        $utils->addBaseToIncudePath();
        $utils->addSrcToIncludePath();
        $utils->setErrorHandler();
        $utils->sendSSLHeaders();
        $utils->sessionStart();
        $utils->setDebug();

        // Construct all other services
        parent::__construct();

        // Now we can use the extends the AppUtils services in the app
        $this->csp->sendCSPHeaders();
        $this->csrf->setCSRFToken(verbs: ['GET'], exclude_paths: ['/account/captcha']);

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
