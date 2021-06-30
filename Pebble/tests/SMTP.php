<?php

include_once "autoload.php";

use Pebble\Config;
use Pebble\SMTP;
use Pebble\Log;



// Add a SMTP.php file to config dir with sthe following variables: 
/*
    return [
        'DefaultFrom' => 'mail@coscms.org',
        'DefaultFromName' => 'mail',
        'Host' => 'smtp.sendgrid.net',
        'Port' => 587,
        'SMTPAuth' => true,
        'SMTPSecure' => 'tls',
        'Username' => 'user@domain.com',
        'Password' => 'password',
        'SMTPDebug' => 0
    ];
*/
// Read SMTP info
$config_dir = dirname(__FILE__) . '/../../config';
Config::readConfig($config_dir);

$config_dir = dirname(__FILE__) . '/../../config-live';
Config::readConfig($config_dir);

// Set log dir
Log::setDir(dirname(__FILE__) . "/../../logs");

// Change default from and fromName 
// $smtp = new SMTP('mail@coscms.org', 'dennis');

// Or just read from configuration
$smtp = new SMTP();


$smtp->send(
    'dennis@sharksmedia.dk',
    'Here is the subject',
    'This is a test message in text',
    'This is the HTML message body <b>in bold!</b>',
    ['./testfile.txt']
);

die('test');

$smtp->sendMarkdown(
    'dennis@sharksmedia.dk',
    'Here is the subject',
    // Text will be markdown message and HTML will be markdown parsed message
    '### This is a test message in markdown
    
    Hello Dennis. How is this looking?
    ', 

    ['./testfile.txt']
);
