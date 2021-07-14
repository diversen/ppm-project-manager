<?php

namespace Pebble;

use Pebble\DB;
use Exception;

class DBInstance {

    private static $DB = null;

    /**
     * Create a database connection
    * `DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);`
     */
    public static function connect(string $url, string $username = '', string $password = '', array $options = []) {
        if (!self::$DB) {
            self::$DB = new DB($url, $username, $password, $options);
        }
    }

    public static function close() {
        self::$DB = null;
    }

    /**
     * Get an instance of \Pebble\DB
     * `DBInstance::get();` 
     * @return \Pebble\DB 
     */
    public static function get()
    {
        if (!self::$DB) {
            throw new Exception('Before getting the database object, you need to connect using DBInstance::connect');
        }
        return self::$DB;
    }
}
