<?php

declare(strict_types=1);

use Pebble\Path;
use Monolog\Logger;

use Monolog\Handler\StreamHandler;

$logger = new Logger('base');
$base_path = Path::getBasePath();
$logger->pushHandler(new StreamHandler($base_path . '/logs/main.log', Logger::DEBUG));

return [
    'logger' => $logger,
];

// RotatingFileHandler
// use Monolog\Handler\RotatingFileHandler;
// $rotating_handler = new RotatingFileHandler($base_path . '/logs/main.log', 365, Logger::DEBUG);
// $logger->pushHandler($rotating_handler);


