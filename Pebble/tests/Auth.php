<?php

use Pebble\DBInstance;
use Pebble\Auth;
use Pebble\Config;

include_once "autoload.php";

$config_dir = dirname(__FILE__) . '/../../config';
Config::readConfig($config_dir);

new DBInstance(Config::get('DB.url'), Config::get('DB.username'), Config::get('DB.password'));

$db = DBInstance::get();
$db->prepareExecute('DELETE FROM `auth` WHERE email = ?', ['test@mail.com']);

$auth = new Auth();

// Create a auth row
$res = $auth->create('test@mail.com', 'som_secret_password');  
var_dump($res);

// This will NOT get an auth row as the account needs to be verified
$res = $auth->authenticate('test@mail.com', 'som_secret_password');
var_dump($res);

$authRow = $auth->getByEmail('test@mail.com');
var_dump($authRow);

// verify with RANDOM key. E.g. send this by mail so the user can verify his email adresse.
$res = $auth->verifyKey($authRow['random']);
var_dump($res);

// Authenticate. And now you will get an auth row
$res = $auth->authenticate('test@mail.com', 'som_secret_password');
var_dump($res);

$res = $auth->updatePassword($res['id'], 'new_super_cool_password');
var_dump($res);

