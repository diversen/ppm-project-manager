<?php declare (strict_types = 1);

namespace Pebble;

class Headers
{

    public static function getHttpsHeaders() {
        
        $headers[] = 'HTTP/1.1 301 Moved Permanently';

        $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $headers[] = 'Location: ' . $location;
        
        return $headers;
    }

    public static function redirectToHttps()
    {
        
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
            foreach(self::getHttpsHeaders() as $header) {
                header($header);
            }
            exit();
        }
    }
}
