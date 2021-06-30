<?php

/**
 * Autoloader. Should just be included
 */
spl_autoload_register(function($className)
{
    $classPath = str_replace("\\", '/', $className) . '.php';
    if (file_exists($classPath)) {
        require_once $classPath;
    }  
});
