<?php declare(strict_types=1);

namespace Pebble;

use Pebble\File;

use Exception;

class Config
{

    /**
     * Var holding all config variables
     */
    public static $variables = [];

    /**
     * Var holding all sections
     */
    public static $sections = [];

    /**
     * Get filename without extension
     */
    private static function getFilename(string $file) : string
    {
        $info = pathinfo($file);
        return $info['filename'];
    }

    /**
     * Read all configuration files (php files) from dir.
     */
    public static function readConfig(string $dir)
    {
        if (!file_exists($dir)) {
            throw new Exception('Before reading a config dir, you need to make sure the dir exist: ' . $dir);
        }

        $files = File::dirToArray($dir);

        foreach ($files as $file) {

            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext !== 'php') {
                continue;
            }

            $config_file = $dir . "/$file";
            $config_array = require($config_file);

            // var_dump($config_array);
            $filename = self::getFilename($file);
            self::$sections[$filename] = $config_array;
            self::$variables = array_merge(self::$variables, self::getSectionByName($filename, $config_array));
            
        }
    }

    /**
     * Get config section by name, e.g. config/SMTP.php will get the section name SMTP
     * And the array returned will be an array where 'SMTP.' is prepended
     */
    private static function getSectionByName(string $section, array $configAry) : array
    {
        $ret = [];
        foreach ($configAry as $key => $value) {
            $ret[$section . '.' . $key] = $value;
        }
        return $ret;
    }

    /**
     * Get single configuration variable
     */
    public static function get(string $key)
    {
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }
        return null;
    }

    /**
     * Get section of configuration, e.g. DB
     */
    public static function getSection(string $key) : array
    {
        if (isset(self::$sections[$key])) {
            return self::$sections[$key];
        }
        return [];
    }
}
