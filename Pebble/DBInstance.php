<?php declare(strict_types=1);

namespace Pebble;

use Exception;
use \Pebble\DB;

/**
 * Get a single instance of \Pebble\DB in order to use a single database connection
 */
class DBInstance
{

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

