<?php declare(strict_types=1);

namespace Pebble;

use \Exception;
use \Diversen\Lang;

class CSRF
{

    public function getToken (string $type = 'ajax') {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_type'] = $type;

        $token = $_SESSION['csrf_token'];
        return $token;
    }

    public function validateToken() {
        
        // Check all POSTS
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

    public function validateTokenJSONError() {
        if (!$this->validateToken()) {
            echo json_encode(['error' => Lang::translate('CSRF token was invalid. Please try again.')]);
            exit();
        }
    }
}
