<?php

include_once "autoload.php";

use \Pebble\DBInstance;
use \Pebble\Log;

Log::setDir('logs');

function db_get_bogus_driver() {

    try {

        $sqlite_url = 'XXXsqlite::memory:';
        DBInstance::connect($sqlite_url);
    } catch (Throwable $e) {
        return $e;
    }

    return DBInstance::get();
}

Test::equal_type('Bogus driver', get_class(db_get_bogus_driver()), Exception::class );

function db_get_valid_driver() {
    try {
        $sqlite_url = 'sqlite::memory:';
        DBInstance::connect($sqlite_url);
        
    } catch (Throwable $e) {
        return $e;
    }
    
    return DBInstance::get();
}

Test::equal_type('Valid driver', get_class(db_get_valid_driver()), \Pebble\DB::class);

function db_invalid_prepare_execute() {

    $db = db_get_valid_driver();

    // Bogus SQL
    $table = <<<EOF
    CREATE xxTABLE bogus sql
    EOF;

    try {
        $db->prepareExecute($table);
    } catch (Exception $e) {
        return $e;
    }
    return new stdClass();
}

Test::equal_type('prepareExecute invalid SQL', get_class(db_invalid_prepare_execute()), \PDOException::class);


function db_valid_prepare_execute() {

    $db = db_get_valid_driver();

    $table = <<<EOF
    CREATE TABLE IF NOT EXISTS account (
        id INTEGER PRIMARY KEY, 
        email TEXT, 
        password TEXT)
    EOF;

    try {
        $res = $db->prepareExecute($table);
    } catch (Exception $e) {
        return $e;
    }

    return $res;

}

Test::equal_type('prepareExecute valid SQL', db_valid_prepare_execute(), true);

$table = <<<EOF
CREATE TABLE IF NOT EXISTS account (
    id INTEGER PRIMARY KEY, 
    email TEXT, 
    password TEXT)
EOF;

die;

// $db->prepareExecute($table);

// Use strings as keys
$values = array(':email' => 'test@test.dk', ':password' => 'secret');

// Execute with ':' pattern
$sql = "INSERT INTO account (`email`, `password`) VALUES (:email, :password)";

// Return true or false
$res = $db->prepareExecute($sql, $values);
$rowCount = $db->rowCount();

if ($rowCount) {
    echo "Row count affected: " . $rowCount . PHP_EOL;
    echo "Record inserted"  . PHP_EOL;
    echo "With ID: " . $db->lastInsertId() . PHP_EOL;
}

// Execute pattern with '?' (ints as array keys)
$sql2 = "INSERT INTO account (`email`, `password`) VALUES (?, ?)";
$db->prepareExecute($sql2, ['test@test.dk', 'secret2']);

// Fetch singl row. In this case the last inserted row
$sql = "SELECT * FROM account WHERE id = :id";
$rows = $db->prepareFetch($sql, array('id' => $db->lastInsertId()));

var_dump($rows);

// Fetch all rows
$sql = "SELECT * FROM account";
$rows = $db->prepareFetchAll($sql);

var_dump($rows);
