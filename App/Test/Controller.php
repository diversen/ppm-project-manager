<?php declare(strict_types=1);

namespace App\Test;

use Pebble\Template;
use Pebble\DBInstance;
use Exception;

class Controller {
    public function index ($params) {
        var_dump($_COOKIE);
        // echo self::response(['test' => 100, 'test2' => 'hello world']);
    }

     /**
     * Test error
     */
    public static function response(mixed $value, int $flags = 0, int $depth= 512 ) {
        header('Content-Type: application/json');
        $res = json_encode($value, $flags, $depth);
        if ($res === false){
            throw new Exception('JSON could not be encoded');
        }

        return $res;
    }
}
