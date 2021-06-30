<?php

include_once "autoload.php";

use \Pebble\DBInstance;

// Same as the DB class, but use DB as a single instance
$sqlite_url = 'sqlite::memory:';

// Init
new DBInstance($sqlite_url);

$db = DBInstance::get();

$table = <<<EOF
CREATE TABLE IF NOT EXISTS account (
    id INTEGER PRIMARY KEY, 
    email TEXT, 
    password TEXT)
EOF;

$res = $db->prepareExecute($table);
var_dump($res);

// For all public methods of DB class. See DB.php