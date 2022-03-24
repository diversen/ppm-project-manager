<?php

namespace App;

use Pebble\Config;
use Pebble\Auth;
use Pebble\DB;
use Pebble\Session;
use Pebble\Headers;
use Pebble\JSON;
use Pebble\Router;
use App\AppACL;
use App\Settings\SettingsModel;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Aidantwoods\SecureHeaders\SecureHeaders;
use Diversen\Lang;

use ErrorException;

/**
 * AppMain contains the application
 * 
 */
class AppMain
{
    private $basePath = null;

    /**
     * @var Pebble\Config
     */
    public static $config = null;

    /**
     * @var Monolog\Logger;
     */
    public static $log = null;

    /**
     * @var Pebble\DB
     */
    public static $db = null;

    /**
     * @var Pebble\Auth
     */
    public static $auth = null;

    /**
     * @var App\AppAcl
     */
    public static $appAcl = null;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__);
    }

    public function setErrorHandler()
    {

        // Throw on all kind of errors and notices
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    public function getConfig()
    {
        if (!self::$config) {
            self::$config = new Config();
            self::$config->readConfig($this->basePath . '/config');
            self::$config->readConfig($this->basePath . '/config-locale');
        }

        return self::$config;
    }

    public function getLog()
    {
        if (!self::$log) {
            $log = new Logger('base');
            $log->pushHandler(new StreamHandler($this->basePath . '/logs/main.log', Logger::DEBUG));
            self::$log = $log;
        }

        return self::$log;
    }

    public function getDB()
    {
        $db_config = $this->getConfig()->getSection('DB');
        if (!self::$db) {
            self::$db = new DB($db_config['url'], $db_config['username'], $db_config['password']);
        }
        return self::$db;
    }

    public function getAuth()
    {
        if (!self::$auth) {
            $auth_cookie_settings = $this->getConfig()->getSection('Auth');
            self::$auth = new Auth($this->getDB(), $auth_cookie_settings);
        }
        return self::$auth;
    }

    public function getAppACL()
    {
        if (!self::$appAcl) {
            $auth_cookie_settings = $this->getConfig()->getSection('Auth');
            self::$appAcl = new AppAcl($this->getDB(), $auth_cookie_settings);
        }

        return self::$appAcl;
    }

    public function sendHeaders()
    {
        $config = $this->getConfig();
        if ($config->get('App.force_ssl')) {
            Headers::redirectToHttps();
        }

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

    public function sessionStart()
    {

        // Start session. E.g. Flash messages
        Session::setConfigSettings($this->getConfig()->getSection('Session'));
        session_start();
    }

    public function setIncludePath()
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
    }

    public function run()
    {
        // Define all routes
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


    public function setupIntl()
    {
        $this->setIncludePath();

        $settings = new SettingsModel();

        $auth_id = $this->getAuth()->getAuthId();
        $user_settings = $settings->getUserSetting($auth_id, 'profile');

        $timezone = $user_settings['timezone'] ?? $this->getConfig()->get('App.timezone');
        $language = $user_settings['language'] ?? $this->getConfig()->get('Language.default');

        date_default_timezone_set($timezone);

        // Setup translations
        $translations = new Lang();
        $translations->setSingleDir(".");
        $translations->loadLanguage($language);
    }

    public function setDebug()
    {
        if ($this->getConfig()->get('App.env') === 'dev') {
            JSON::$debug = true;
        }
    }
}
