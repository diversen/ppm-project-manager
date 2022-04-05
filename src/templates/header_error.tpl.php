<?php declare(strict_types=1);

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title>
    <meta name="description" content="<?=$description?>">
    <meta name="theme-color" content="#ffffff">
    
    <?php

    if ($use_theme_dark_mode): ?>
    <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/dark.min.css">
    <?php else: ?>
    <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/light.min.css">
    <?php endif; ?>

    <link rel="stylesheet" href="/css/default.css?v=1.1">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    <link rel="manifest" href="/assets/manifest.json?v=1.1">
</head>
<body>

<a title="" href="/"><img src="/assets/logo.svg"></img></a>

<?php
