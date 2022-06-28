<?php declare (strict_types = 1);

require_once "vendor/autoload.php";

use Diversen\MinimalCli;
use Pebble\CLI\User;
use Pebble\CLI\DB;
use Pebble\CLI\Migrate;
use Pebble\CLI\Translate;
use Pebble\Service\ConfigService;

$config = (new ConfigService())->getConfig();

$cli = new MinimalCli();
$cli->commands = [
    'user' => new User($config),
    'db' => new DB($config),
    'migrate' => new Migrate($config),
    'translate' => new Translate($config),
];

$cli->runMain();
