<?php

include_once "autoload.php";

use Pebble\Template;

echo Template::getOutput('view.php', ['name' => 'Dennis']);
