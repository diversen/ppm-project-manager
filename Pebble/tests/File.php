<?php

use \Pebble\File;

include_once "autoload.php";

// File helpers function

// Get all file names in e.g. current dir.
$files = File::dirToArray('.');
var_dump($files);