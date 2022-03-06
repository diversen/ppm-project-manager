<?php

namespace App;

use Pebble\Config;
use Pebble\Log\File as FileLog;
use Pebble\Auth;
use Pebble\DB;
use App\AppACL;

/**
 * AppMain is a class that returns instances of objects where we only want one object
 */
class AppMain {

    private $basePath = null;

    /**
     * @var Pebble\Config
     */
    public static $config = null;

    /**
     * @var Pebble\FileLog
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
    
    public function __construct() {
        $this->basePath = dirname(__DIR__) . '/..';
    }

    public function getConfig() {
        if(!self::$config) {
            self::$config = new Config();
            self::$config->readConfig($this->basePath . '/config');
            self::$config->readConfig($this->basePath . '/config-locale');
        }

        return self::$config;
    }

    public function getLog() {
        if(!self::$log) {
            self::$log = new FileLog(['log_dir' => $this->basePath . '/logs']);
        }

        return self::$log;
        
    }

    public function getDB() {
        
        $db_config = $this->getConfig()->getSection('DB');
        if (!self::$db) {
            self::$db = new DB($db_config['url'], $db_config['username'], $db_config['password']);
        }
        return self::$db;

    }

    public function getAuth() {

        
        if (!self::$auth) {
            $auth_cookie_settings = $this->getConfig()->getSection('Auth');
            self::$auth = new Auth($this->getDB(), $auth_cookie_settings);
        }
        return self::$auth;
    }

    public function getAppACL() {
        if (!self::$appAcl){
            $auth_cookie_settings = $this->getConfig()->getSection('Auth');
            self::$appAcl = new AppAcl($this->getDB(), $auth_cookie_settings);
        }

        return self::$appAcl;

    }
}
