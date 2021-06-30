<?php

namespace Pebble;

class ExceptionTrace
{

    public static function get($e)
    {

        // Log error to file
        $exception_str =
        'Message: ' . $e->getMessage() . "\n" .
        'In: ' . $e->getFile() . ' (' . $e->getLine() . ')' . "\n" .
        'Trace: ' . $e->getTraceAsString() . "\n";

        return $exception_str;

    }

}
