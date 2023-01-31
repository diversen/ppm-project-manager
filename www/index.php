<?php

// Include vendor loaded packages
require_once "../vendor/autoload.php";

use Pebble\App\AppExec;
use App\AppMain;
use App\Error\Controller as ErrorHandler;

$pebble_exec = new AppExec();
$pebble_exec->setErrorController(ErrorHandler::class);

$pebble_exec->setApp(AppMain::class);
$pebble_exec->run();
