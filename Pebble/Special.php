<?php declare(strict_types=1);

namespace Pebble;

class Special
{

    /**
     * Encode html special char on an array
     * It will only encode strings and numeric values
     * Objects will keep value
     */
    public static function encodeAry(array $values) : array
    {

        foreach ($values as $key => $val) {
            if (is_array($val)) {
                $values[$key] = self::encodeAry($val);
            } else {
                $values[$key] = self::encodeStr($val);
            }
        }

        return $values;
    }

    /**
     * htmlspecialchars on strings
     * Any other values will just be returned
     */
    public static function encodeStr($str)
    {
        
        // Convert numeric values to strings
        if (is_numeric($str)){
            $str = strval($str);
        }


        if (is_string($str)) {
            return htmlspecialchars($str, ENT_COMPAT, 'UTF-8'); 
        }

        return $str;


    }

    /**
     * Decode a string
     */
    public static function decodeStr($str): string {
        return htmlspecialchars_decode($str, ENT_COMPAT);
    }
}
