<?php declare (strict_types = 1);

if (isset($_COOKIE['theme_dark_mode'])) {
    $use_theme_dark_mode = $_COOKIE['theme_dark_mode'];
} else {
    $use_theme_dark_mode = false;
}

if (!isset($title)) {
    $title = '';
}

if (!isset($description)) {
    $description = '';
}

require 'App/templates/helpers.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title>
    <meta name="description" content="<?=$description?>">
    <meta name="theme-color" content="#ffffff">
    
    <?php 
    
    if ($use_theme_dark_mode): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/dark.css">
    <?php else: ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <?php endif; ?>
    

    <link rel="stylesheet" href="/App/templates/css/default.css">
    <script src="/App/templates/js/pebble.js"></script>
    <meta name="google-signin-client_id" content="509607769994-8m5qbtkkg1f071eafj7slrhb9mq0a9a5.apps.googleusercontent.com">
</head>
<body>
<h1><a href="/">Task Manager</a></h1>

<?php

require 'App/templates/flash.tpl.php';