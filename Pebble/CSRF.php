<?php declare(strict_types=1);

namespace Pebble;

use \Exception;
use \Diversen\Lang;

class CSRF
{
    /**
     * Sets a SESSION token
     */
    public function getToken () {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $token = $_SESSION['csrf_token'];
        return $token;
    }

    /**
     * Validates the SESSION token against POST value
     * It also unsets the POST csrf_token
     */
    public function validateToken() {
        
        if (!empty($_POST)) {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                $res = false;       
            } else {
                $res = true;
            }
        }
        unset($_POST['csrf_token']);
        return $res;
    }
}
