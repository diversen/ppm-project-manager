<?php

declare(strict_types=1);

namespace App\Main;

use Pebble\Config;
use Pebble\Auth;
use Pebble\DB;
use Pebble\Session;
use Pebble\Headers;
use Pebble\JSON;
use Pebble\HTTP\AcceptLanguage;

use App\AppACL;
use App\Settings\SettingsModel;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Diversen\Lang;

use ErrorException;

/**
 * AppMain contains the application logic.
 * It initializes the application logic and runs it.
 */
class AppBase
{
    public $base_path = null;

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
        $this->base_path = dirname(dirname(__DIR__));
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
            self::$config->readConfig($this->base_path . '/config');
            self::$config->readConfig($this->base_path . '/config-locale');
        }

        return self::$config;
    }

    public function getLog()
    {
        if (!self::$log) {
            $log = new Logger('base');
            $log->pushHandler(new StreamHandler($this->base_path . '/logs/main.log', Logger::DEBUG));
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
    }

    /**
     * Start session with configuraton fra Session config
     */
    public function sessionStart()
    {
        Session::setConfigSettings($this->getConfig()->getSection('Session'));
        session_start();
    }

    /**
     * Set include path to 'src' dir in order to easy include files from src dir (e.g. templates)
     */
    public function setIncludePath(string $dir)
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . $dir);
    }

    /**
     * Load user language and timezone if set else load default language
     * Init translations
     */
    public function setupIntl()
    {
        $settings = new SettingsModel();

        $auth_id = $this->getAuth()->getAuthId();
        $user_settings = $settings->getUserSetting($auth_id, 'profile');

        $timezone = $user_settings['timezone'] ?? $this->getConfig()->get('App.timezone');
        $language = $user_settings['language'] ?? $this->getRequestLanguage();

        date_default_timezone_set($timezone);

        // Setup translations
        $translations = new Lang();
        $translations->setSingleDir("../src");
        $translations->loadLanguage($language);
    }

    private function getRequestLanguage()
    {
        $default = $this->getConfig()->get('Language.default');
        $supported = $this->getConfig()->get('Language.enabled');

        return AcceptLanguage::getLanguage($supported, $default);
    }

    /**
     * Set some debug
     */
    public function setDebug()
    {
        if ($this->getConfig()->get('App.env') === 'dev') {
            JSON::$debug = true;
        }
    }
}
