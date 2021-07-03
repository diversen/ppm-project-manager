<?php

namespace Pebble;

use Exception;

class JSON {

    /**
     * json_encode wrapper which just add content-type header
     */
    public static function response($value, int $flags = 0, int $depth= 512 ) {
        header('Content-Type: application/json');
        $res = json_encode($value, $flags, $depth);
        if ($res === false){
            throw new Exception('JSON could not be encoded');
        }

        return $res;
    }
}