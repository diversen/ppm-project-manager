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
     * Get config array from a dir and a file
     */
    private static function getConfigArray($dir, $file) {
        
        $config_file = $dir . "/$file";
        $config_array = require($config_file);
        return $config_array;
    }

    /**
     * Only php files a vlid from a configuration dir. 
     * Remove everything that is not a config file.
     */
    private static function getCleanedFiles($files) {

        $files_ret = [];
        foreach($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext !== 'php') {
                continue;
            }
            $files_ret[] = $file;
        }
        return $files_ret;
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
        $files = self::getCleanedFiles($files);

        foreach ($files as $file) {

            $config_array = self::getConfigArray($dir, $file);
            $filename = self::getFilename($file);
            self::$sections[$filename] = $config_array;
            self::$variables = array_merge(self::$variables, self::getSectionByName($filename, $config_array));
            
        }
    }

    /**
     * get a config section. E.g. 'SMTP' will get the configuration from the file 'config/SMTP.php'
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
     * Get e.g. `Config::get('SMTP.username')`
     */
    public static function get(string $key)
    {
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }
        return null;
    }

    /**
     * Get section of configuration, e.g. `Config::get('DB')`
     */
    public static function getSection(string $key) : array
    {
        if (isset(self::$sections[$key])) {
            return self::$sections[$key];
        }
        return [];
    }
}
