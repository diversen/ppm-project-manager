<?php declare (strict_types = 1);

namespace Pebble;

use Pebble\Config;

class App
{

    /**
     * Get scheme and host from App config file
     */
    public function getSchemeAndHost(): string
    {
        $server = Config::getSection('App');
        if (!$server['server_scheme']) {
            $server['server_scheme'] = 'http';
        }

        return $server['server_scheme'] . '://' . $server['server_name'];
    }
}

