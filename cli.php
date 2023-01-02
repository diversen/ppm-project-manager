<?php 

declare (strict_types = 1);

require_once "vendor/autoload.php";

use Diversen\MinimalCli;
use Pebble\CLI\User;
use Pebble\CLI\DB;
use Pebble\CLI\Migrate;
use Pebble\CLI\Translate;
use App\Cron\MoveTasks;

$cli = new MinimalCli();
$cli->header = 'PPM CLI';
$cli->commands = [
    'user' => new User(),
    'db' => new DB(),
    'migrate' => new Migrate(),
    'translate' => new Translate(),
    'cron-move-tasks' => new MoveTasks(),
];

$cli->runMain();
