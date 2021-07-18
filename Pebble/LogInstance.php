<?php declare (strict_types = 1);

namespace Pebble;

use Pebble\Log;

class LogInstance {

    public static $log = null;

    
    public static function init(Log $log) {
        self::$log = $log;
    }

    /**
     * @return Pebble\Log
     */
    public static function get() {
        return self::$log;
    }
}
