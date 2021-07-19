<?php declare(strict_types=1);

namespace Pebble;

class Random
{

    /**
     * Generate a truely random string from a specified length given to random_bytes
     * It returns a hexstring that is `$length * 2` in size 
     */
    public static function generateRandomString(int $length) : string
    {
        $random = bin2hex(random_bytes($length));
        return $random;
    }
}

