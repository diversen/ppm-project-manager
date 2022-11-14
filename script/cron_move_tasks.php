<?php

declare(strict_types=1);

require ("vendor/autoload.php");

use \App\Cron\MoveTasks;

$move_tasks = new MoveTasks();
$users = $move_tasks->run();