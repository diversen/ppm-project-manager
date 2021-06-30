<?php

use Pebble\Special;

include_once "autoload.php";

// Escape array with strings of special chars

var_dump(Special::encodeAry(['<>']));

// Escape a string
echo Special::encodeStr('<>');