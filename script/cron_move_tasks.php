<?php

declare(strict_types=1);

require ("vendor/autoload.php");

use \App\Cron\MoveTasks;

$move_tasks = new MoveTasks();
$move_tasks->run();