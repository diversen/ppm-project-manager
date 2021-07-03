<?php

// Include vendor loaded packages
require_once "vendor/autoload.php";

// Include app auoloaded packages
require_once "autoload.php";

use \App\Error\Controller as ErrorController;
use \Diversen\Lang;
use \Pebble\Config;
use \Pebble\DBInstance;
use \Pebble\Exception\ForbiddenException;
use \Pebble\Exception\NotFoundException;
use \Pebble\Log;
use \Pebble\Router;
use \Pebble\Session;
use \App\Settings\SettingsModel;

// Run the application and check for exceptions and throwable
try {

    $base_path = dirname(__FILE__);

    // Enable first. Then everything can be logged
    Log::setDir($base_path . "/logs");

    // Load config settings
    Config::readConfig($base_path . '/config');

    // Override config settings with locale settings
    Config::readConfig($base_path . '/config-locale');

    // Output all errors in on dev
    if (Config::get('App.env') === 'dev') {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
    } else {
        error_reporting(0);
    }

    // Force SSL
    if (Config::get('App.force_ssl')) {
        \Pebble\Headers::redirectToHttps();
    }

    // Start session. E.g. Flash messages
    $session = new Session();
    $session->setConfigSettings();
    session_start();

    // Get DB configuration
    $db_config = Config::getSection('DB');
    if (!$db_config) {
        throw new Error('You will need to create a DB.php in a loaded cofiguration folder');
    }

    // Connect to DB and create an instance
    DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);
    
    $user_time_zone = SettingsModel::getInstance()->getUserDefaultTimeZone();
    if ($user_time_zone) {
        date_default_timezone_set($user_time_zone);
    } else {
        date_default_timezone_set(Config::get('App.timezone'));
    } 

    // Check user language
    $user_language = SettingsModel::getInstance()->getUserSetting('language');
    if (!$user_language) {
        $user_language = Config::get('Language.default');
    }

    // Setup translations
    $l = new Lang();
    $l->setSingleDir("App");
    $l->loadLanguage($user_language);

    // Define all routes
    $router = new Router();

    \App\Home\Routes::setRoutes($router);
    \App\Account\Routes::setRoutes($router);
    \App\Google\Routes::setRoutes($router);
    \App\Project\Routes::setRoutes($router);
    \App\Overview\Routes::setRoutes($router);
    \App\Task\Routes::setRoutes($router);
    \App\Settings\Routes::setRoutes($router);
    \App\Time\Routes::setRoutes($router);
    \App\Test\Routes::setRoutes($router);
    
    $router->run();


} catch (NotFoundException $e) {

    $error = new ErrorController();
    $error->notFound($e->getMessage());

} catch (ForbiddenException $e) {

    $error = new ErrorController();
    $error->forbidden($e->getMessage());

} catch (Throwable $e) {

    $error = new ErrorController();

    // Log error to file
    $exception_str = \Pebble\ExceptionTrace::get($e);

    // Just in case the Log class is missing a log dir. 
    // Then we use the Log class exception instead. 
    try {
        Log::error($exception_str, 'index');
    } catch (Exception $e) {        
        $error->error($e->getMessage());
        return;
    }
    
    // Display error
    $error = new ErrorController();

    // If we are not on dev display generic error message
    if (Config::get('App.env') !== 'dev') {
        $exception_str = 'A sever error happened. The incidence has been logged.';
    }

    $error->error($exception_str);

} 
