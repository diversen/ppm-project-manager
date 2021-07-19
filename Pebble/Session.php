<?php declare (strict_types = 1);

namespace Pebble;

use Exception;
use \Pebble\Config;

/**
 * Session class just sets default parameters for sessions
 */
class Session
{

    /**
     * Set SESSION defaults from Session Configuration
     */
    public static function setConfigSettings()
    {
        $session_config = Config::getSection('Session');

        if ($session_config) {
            $res = session_set_cookie_params(
                $session_config["lifetime"],
                $session_config["path"],
                $session_config['domain'],
                $session_config["secure"],
                $session_config["httponly"]
            );
            return $res;
        }
    }
}
