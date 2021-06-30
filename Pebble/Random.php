<?php declare(strict_types=1);

namespace Pebble;

class Random
{

    /**
     * Generate a truely random string from a specified length
     */
    public static function generateRandomString(int $length) : string
    {
        $random = bin2hex(random_bytes($length));
        return substr($random, 0, $length);
    }
}

