<?php

declare(strict_types=1);

use Pebble\Path;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

$logger = new Logger('base');
$base_path = Path::getBasePath();

$rotating_handler = new RotatingFileHandler($base_path . '/logs/main.log', 30, Logger::DEBUG);
$logger->pushHandler($rotating_handler);

return [
    'logger' => $logger,
];

// Above uses RotatingFileHandler, below uses StreamHandler
// use Monolog\Handler\StreamHandler;
// $logger->pushHandler(new StreamHandler($base_path . '/logs/main.log', Logger::DEBUG));
