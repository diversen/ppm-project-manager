<?php

declare(strict_types=1);

namespace App;

use Pebble\Router;
use Pebble\App\CommonUtils;
use App\Settings\SetupIntl;
use App\AppUtils;

/**
 * AppMain class. This is the main class for the app.
 * It is used in www/index.php
 */
class AppMain extends AppUtils
{
    public const VERSION = "v2.2.2";
    public static $nonce = '';
    public static $csrf_form_field = '';

    public function __construct()
    {
    }

    public function run()
    {
        $common_utils = new CommonUtils();
        $common_utils->addBaseToIncudePath();
        $common_utils->addSrcToIncludePath();
        $common_utils->setErrorHandler();
        $common_utils->sendSSLHeaders();
        $common_utils->sessionStart();
        $common_utils->setDebug();

        parent::__construct();

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
