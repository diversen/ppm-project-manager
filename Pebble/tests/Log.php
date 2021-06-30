<?php 

use \Pebble\Log;

include_once "autoload.php";

// Set log dir to current ./logs
$base_path = dirname(__FILE__);
Log::setDir($base_path . "/logs");

// Now all logs will be created in some/path/logs dir
// Log something. This will create a log file called 'smtp' (if it does not already exist)
// And logs a message. You can also log array and objects. 
Log::error('Message could not be sent. Mailer Error: 100X', 'smtp');

// The method is called error, but you could just log to a file called 'debug'  instead of 'smtp'
// in order to log some debug.