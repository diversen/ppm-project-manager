<?php

// Include vendor loaded packages
require_once "../vendor/autoload.php";

use Pebble\Autoloader;

$autoload = new Autoloader();
$autoload->setPath(__DIR__);

use Diversen\Lang;

use Pebble\ExceptionTrace;
use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use Pebble\Headers;
use Pebble\JSON;
use Pebble\Router;
use Pebble\Session;

use App\AppMain;
use App\Error\Controller as ErrorController;
use App\Settings\SettingsModel;
use Aidantwoods\SecureHeaders\SecureHeaders;

$app_main = new AppMain();

$error = new ErrorController();

// Run the application and check for exceptions and throwable
try {

    // Throw on all kind of errors and notices
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    if ($app_main->getConfig()->get('App.force_ssl')) {
        Headers::redirectToHttps();
    }

    if ($app_main->getConfig()->get('App.env') !== 'dev') {
        $headers = new SecureHeaders();
        $headers->hsts();
        $headers->csp('default', 'self');
        $headers->csp('img-src', 'data:');
        $headers->csp('img-src', $app_main->getConfig()->get('App.server_url'));
        $headers->csp('script', 'unsafe-inline');
        $headers->csp('script-src', $app_main->getConfig()->get('App.server_url'));
        $headers->csp('default-src', 'unsafe-inline');
        $headers->apply();    
    }
    
    // Start session. E.g. Flash messages
    Session::setConfigSettings($app_main->getConfig()->getSection('Session'));
    session_start();

    // Set timezone and language. Use defaults if not set.
    $settings = new SettingsModel;

    $auth_id = $app_main->getAuth()->getAuthId();
    $user_settings = $settings->getUserSetting($auth_id, 'profile');

    $timezone = $user_settings['timezone'] ?? $app_main->getConfig()->get('App.timezone');
    $language = $user_settings['language'] ?? $app_main->getConfig()->get('Language.default');

    date_default_timezone_set($timezone);

    // Setup translations
    $l = new Lang();
    $l->setSingleDir(".");
    $l->loadLanguage($language);

    if ($app_main->getConfig()->get('App.env') === 'dev') {
        JSON::$debug = true;
    } 

    // Define all routes
    $router = new Router();

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
    
    // If it is a template error then content most likely has been sent to the browser
    // And therefor we can not send a 5xx header.
    $exception_str = ExceptionTrace::get($e);
    $app_main->getLog()->message($exception_str, 'error');

    // If we are not on dev display generic error message
    if ($app_main->getConfig()->get('App.env') !== 'dev') {
        $exception_str = Lang::translate('A sever error happened. The incidence has been logged.');
    }
    echo "<pre>" . $exception_str . "</pre>";

} catch (NotFoundException $e) {

    $app_main->getLog()->message("Page not found: " . $_SERVER['REQUEST_URI'], 'info');
    $error->notFound($e->getMessage());

} catch (ForbiddenException $e) {

    $app_main->getLog()->message("Access denied: " . $_SERVER['REQUEST_URI'], 'warning');
    $error->forbidden($e->getMessage());
    
} catch (Throwable $e) {

    // Log error to file
    $exception_str = ExceptionTrace::get($e);

    // Just in case the Log class is missing a log dir.
    // Then we use the Log class exception instead.
    // Or if using DBLog without a connection
    try {

        $app_main->getLog()->message($exception_str, 'error');
    } catch (Exception $e) {
        $error->error($e->getMessage());
        return;
    }

    // If we are not on dev display generic error message
    if ($app_main->getConfig()->get('App.env') !== 'dev') {
        $exception_str = Lang::translate('A server error happened. The incidence has been logged.');
    }

    $error->error($exception_str);
}
