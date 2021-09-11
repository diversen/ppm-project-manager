<?php

require_once "vendor/autoload.php";

use diversen\MinimalCli;
use Pebble\CLI\User;

$cli = new MinimalCli();
$user_command = new User();
$cli->commands = ['user' => $user_command];
$cli->runMain();