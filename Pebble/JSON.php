<?php declare (strict_types = 1);

namespace Pebble;

use Exception;

class JSON {

    /**
     * json_encode wrapper which just add content-type header
     */
    public static function response($value, int $flags = 0, int $depth= 512, $send_header = true ) {

        if ($send_header) {
            header('Content-Type: application/json');
        }
        
        $res = json_encode($value, $flags, $depth);
        if ($res === false){
            throw new Exception('JSON could not be encoded');
        }

        return $res;
    }
}