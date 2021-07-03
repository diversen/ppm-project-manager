<?php declare(strict_types=1);

namespace Pebble;

use Exception;

class Log
{

    /**
     * Var holding dirs where to place log files
     */
    private static $dir = null;

    /**
     * Set path to log dir
     */
    public static function setDir(string $dir)
    {
        self::$dir = $dir;
    }

    /**
     * Log an error to a log file. 'Type' will log message to file named `$type`.log
     */
    public static function error($message, string $type)
    {

        if (!self::$dir || !is_writable(self::$dir)) {
            throw new Exception('\Pebble\Log tried to write to a file. Remeber to init the Log class using the call \Log::setDir($dir). This dir needs to be writable');
        }

        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        $date = date('Y-m-d');
        $log_dir = self::$dir . '/' . $date;

        if (!is_dir($log_dir)) {
            $res = mkdir($log_dir, 0777, true);
            if (!$res) {
                throw new Exception('\Pebble\Log could not make dir ' . $log_dir);
            }
        }

        $log_file = $log_dir . '/' . $type . '.log';

        // Generate message
        $time_stamp = date('Y-m-d H:i:s');
        $log_message = $time_stamp . PHP_EOL . $message . PHP_EOL;
        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);

    }
}

