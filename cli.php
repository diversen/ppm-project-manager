<?php declare (strict_types = 1);

require_once "vendor/autoload.php";

use Diversen\MinimalCli;
use Pebble\CLI\User;
use Pebble\CLI\DB;
use Pebble\CLI\Migrate;
use Pebble\CLI\Translate;
use Pebble\Autoloader;
use Pebble\Config;

$autoloader = new Autoloader();
$autoloader->setPath(__DIR__);

// Load config settings
Config::readConfig('./config');

// Load config settings
Config::readConfig('./config-locale');

$cli = new MinimalCli();
$user_command = new User();
$cli->commands = [
    'user' => new User(),
    'db' => new DB(),
    'migrate' => new Migrate(),
    'translate' => new Translate(),
];

$cli->runMain();
