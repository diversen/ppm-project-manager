<?php

use \Pebble\Random;

include_once "autoload.php";

// Generate a truely random string
$random = Random::generateRandomString(128);
print_r($random);