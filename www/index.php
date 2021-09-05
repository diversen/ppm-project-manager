<?php

// Include vendor loaded packages
require_once "../vendor/autoload.php";

use Pebble\Autoloader;

$autoload = new Autoloader();
$autoload->setPath(__DIR__);

use App\Error\Controller as ErrorController;
use App\Settings\SettingsModel;
use Diversen\Lang;
use Pebble\Config;
use Pebble\DBInstance;
use Pebble\ExceptionTrace;
use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use Pebble\Headers;
use Pebble\Log;
use Pebble\LogInstance;
use Pebble\Router;
use Pebble\Session;
use Pebble\Auth;

$error = new ErrorController();

// Run the application and check for exceptions and throwable
try {

    // Throw on all kind of errors and notices
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    $base_path = dirname(__DIR__);

    // Load config settings
    Config::readConfig($base_path . '/config');

    // Override config settings with locale settings
    Config::readConfig($base_path . '/config-locale');

    // Make a log instance
    $log = new Log([
        // 'stream' => 'php://stderr',
        'log_dir' => $base_path . '/logs',
    ]);

    LogInstance::init($log);

    // Force SSL
    if (Config::get('App.force_ssl')) {
        Headers::redirectToHttps();
    }

    // Start session. E.g. Flash messages
    Session::setConfigSettings();
    session_start();

    // Get DB configuration
    $db_config = Config::getSection('DB');
    if (!$db_config) {
        throw new Error('You will need to create a DB.php in a loaded cofiguration folder');
    }

    // Connect to DB and create an instance
    DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);

    // Set timezone and language. Use defaults if not set.
    $settings = new SettingsModel;
    $auth_id = Auth::getInstance()->getAuthId();
    $user_settings = $settings->getUserSetting($auth_id, 'profile');

    $timezone = $user_settings['timezone'] ?? Config::get('App.timezone');
    $language = $user_settings['language'] ?? Config::get('Language.default');

    date_default_timezone_set($timezone);

    // Setup translations
    $l = new Lang();
    $l->setSingleDir("App");
    $l->loadLanguage($language);

    // Define all routes
    $router = new Router();

    $router->addClass(App\Account\Controller::class);
    $router->addClass(App\Home\Controller::class);
    $router->addClass(App\Google\Controller::class);
    $router->addClass(App\Overview\Controller::class);
    $router->addClass(App\Project\Controller::class);
    $router->addClass(App\Settings\Controller::class);
    $router->addClass(App\Task\Controller::class);
    $router->addClass(App\Time\Controller::class);

    $router->run();

} catch (TemplateException $e) {

    // If it is a template error then content most likely has been sent to the browser
    // And therefor we can not send a 5xx header.
    $exception_str = ExceptionTrace::get($e);
    LogInstance::get()->message($exception_str, 'error');

    // If we are not on dev display generic error message
    if (Config::get('App.env') !== 'dev') {
        $exception_str = Lang::translate('A sever error happened. The incidence has been logged.');
    }
    echo "<pre>" . $exception_str . "</pre>";


} catch (NotFoundException $e) {

    LogInstance::get()->message("Page not found: " . $_SERVER['REQUEST_URI'], 'info');
    $error->notFound($e->getMessage());

} catch (ForbiddenException $e) {

    LogInstance::get()->message("Access denied: " . $_SERVER['REQUEST_URI'], 'warning');
    $error->forbidden($e->getMessage());

} catch (Throwable $e) {

    

    // Log error to file
    $exception_str = ExceptionTrace::get($e);

    // Just in case the Log class is missing a log dir.
    // Then we use the Log class exception instead.
    try {

        LogInstance::get()->message($exception_str, 'error');
    } catch (Exception $e) {
        $error->error($e->getMessage());
        return;
    }

    // If we are not on dev display generic error message
    if (Config::get('App.env') !== 'dev') {
        $exception_str = Lang::translate('A sever error happened. The incidence has been logged.');
    }

    $error->error($exception_str);

}
