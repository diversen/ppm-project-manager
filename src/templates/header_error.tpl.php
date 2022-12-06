<?php declare(strict_types=1);

use App\AppMain;

$use_theme_dark_mode = $_COOKIE['theme_dark_mode'] ?? false;

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
    <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/dark.min.css?version=<?=AppMain::VERSION?>">
    <?php else: ?>
    <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/light.min.css?version=<?=AppMain::VERSION?>">
    <?php endif; ?>

    <link rel="stylesheet" href="/css/default.css?v=1.1">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon_io/apple-touch-icon.png?version=<?=AppMain::VERSION?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon_io/favicon-32x32.png?version=<?=AppMain::VERSION?>">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon_io/favicon-16x16.png?version=<?=AppMain::VERSION?>">
    <link rel="manifest" href="/assets/manifest.json?version=<?=AppMain::VERSION?>">
</head>
<body>

<div class="logo">
<a title="" href="/">
    <img 
        src="/assets/logo.png?version=<?=AppMain::VERSION?>">
    </img>
</a>
</div>

<div class="page">

<?php
