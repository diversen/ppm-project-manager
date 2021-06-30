<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../../vendor/autoload.php";

spl_autoload_register(function($className)
{
    $classPath = str_replace("\\", '/', $className) . '.php';
    $classPath = '../../' . $classPath;
    
    if (file_exists($classPath)) {
        include_once $classPath;
    }
});
