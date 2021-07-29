<?php declare (strict_types = 1);

namespace App\Test;

use Exception;
use Pebble\ACLRole;
use Pebble\Headers;

class Controller
{
    public function redirect_to_https($params)
    {

        Headers::redirectToHttps();

    }

    /**
     * Test error
     */
    public static function response(mixed $value, int $flags = 0, int $depth = 512)
    {
        header('Content-Type: application/json');
        $res = json_encode($value, $flags, $depth);
        if ($res === false) {
            throw new Exception('JSON could not be encoded');
        }

        return $res;
    }

    public function test()
    {
        $aclr = new ACLRole();

        $aclr->hasRoleOrThrow(['auth_id' => $aclr->getAuthId(), 'right' => 'admin']);
        // $aclr->setRole(['auth_id' => $aclr->getAuthId(), 'right' => 'admin'] );
    }

    public function phpinfo() {
        // phpinfo();

        // 7.2.24
    }
}
