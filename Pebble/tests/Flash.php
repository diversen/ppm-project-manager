<?php

use \Pebble\Flash;

include_once "autoload.php";

// Session needs to be started. E.g. in index.php
session_start();

// Message is the first param
// Type is the second param ['info', 'success', 'warning', 'error']
Flash::setMessage('Great work!', 'success');

// Get all flash messages and unset messages in $_SESSION
$flash = Flash::getMessages();

print_r($flash);
