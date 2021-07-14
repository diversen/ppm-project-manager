<?php declare (strict_types = 1);

namespace Pebble;

use Pebble\Log;

class LogInstance {

    public static $log = null;

    
    public static function init(Log $log) {
        self::$log = $log;
    }

    public static function message($message, string $type = 'debug', ?string $custom_log_file = null): void {
        self::$log->message($message, $type, $custom_log_file);
    }
}
