<?php

// Include vendor loaded packages
require_once "../vendor/autoload.php";

use Pebble\Autoloader;

// $autoload = new Autoloader();
// $autoload->setPath(__DIR__);

use Pebble\ExceptionTrace;
use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use Pebble\Router;

use App\AppMain;
use App\Error\Controller as ErrorController;
use Diversen\Lang;

try {
    $app_main = new AppMain();
    $error = new ErrorController();

    $app_main->sendHeaders();
    $app_main->sessionStart();
    $app_main->setupIntl();
    $app_main->setDebug();

    // Define all routes
    $router = new Router();
    $router->addClass(App\Test\Controller::class);
    $router->addClass(App\Account\ControllerExt::class);
    $router->addClass(App\Home\Controller::class);
    $router->addClass(App\Google\Controller::class);
    $router->addClass(App\Overview\Controller::class);
    $router->addClass(App\Project\Controller::class);
    $router->addClass(App\Settings\Controller::class);
    $router->addClass(App\Task\Controller::class);
    $router->addClass(App\Time\Controller::class);
    $router->addClass(App\Error\Controller::class);
    $router->addClass(App\TwoFactor\Controller::class);

    $router->run();
} catch (TemplateException $e) {
    $exception_str = ExceptionTrace::get($e);
    $app_main->getLog()->error('App.index.exception', ['exception' => $exception_str]);

    if ($app_main->getConfig()->get('App.env') !== 'dev') {
        $exception_str = Lang::translate('A sever error happened. The incidence has been logged.');
    }

    echo "<pre>" . $exception_str . "</pre>";
} catch (NotFoundException $e) {
    $app_main->getLog()->notice("App.index.not_found ", ['url' => $_SERVER['REQUEST_URI']]);
    $error->notFound($e->getMessage());
} catch (ForbiddenException $e) {

    // These exceptions are logged in controllers
    $app_main->getLog()->notice("App.index.forbidden", ['url' => $_SERVER['REQUEST_URI']]);
    $error->forbidden($e->getMessage());
} catch (Throwable $e) {
    $exception_str = ExceptionTrace::get($e);

    try {
        $app_main->getLog()->error('App.index.exception', ['exception' => $exception_str]);
    } catch (Exception $e) {
        $error->error($e->getMessage());
        return;
    }

    if ($app_main->getConfig()->get('App.env') !== 'dev') {
        $exception_str = Lang::translate('A server error happened. The incidence has been logged.');
    }

    $error->error($exception_str);
}
