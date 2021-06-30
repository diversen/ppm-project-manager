<?php declare(strict_types=1);

namespace Pebble;

use \Pebble\Config;

/**
 * Session class just sets default parameters for sessions
 */
class Session
{

    /**
     * Set SESSION defaults from Session Configuration
     */
    public function setConfigSettings()
    {
        $session = Config::getSection('Session');
        
        foreach ($session as $key => $val) {
            $ini = 'session.' . $key;
            ini_set($ini, $val);
        }
    } 
}

