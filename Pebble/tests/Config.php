<?php


use \Pebble\Config;

// Read a dir of configuration
// This could be in index.php
include_once "autoload.php";

$config_dir = dirname(__FILE__) . '/../../config';
Config::readConfig($config_dir);

// Now you have all configuration found in `config`
// A config file could look like this, `config/SMTP.php`

/*
return [
    'SMTPDebug' => 0, 
    'Host' => 'smtp.sendgrid.net', 
    'SMTPAuth' => true, 
    'Username' => 'user@mail.com',
    'Password' => 'password',
    'SMTPSecure' => 'tls',
    'Port' => 587,
    'DefaultFrom' => 'from@mail.com',
    'DefaultFromName' => 'My name'
];*/

// Get all SMTP configuration as an array
$SMTP = Config::getSection('SMTP');
var_dump($SMTP);

// Or get single item. The file name '.' a key in in the configuration array): 
$port = Config::get('SMTP.Port');
var_dump($port);