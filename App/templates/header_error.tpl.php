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
    <link rel="stylesheet" href="/App/templates/css/water/dark.min.css">
    <?php else: ?>
    <link rel="stylesheet" href="/App/templates/css/water/light.min.css">
    <?php endif; ?>

    <link rel="manifest" href="/App/templates/assets/manifest.json">
    <link rel="stylesheet" href="/App/templates/css/default.css?v=3">
    <link rel="icon" sizes="192x192" href="/App/templates/assets/ppm-logo-192x192.png">
    <script src="/App/templates/js/pebble.js"></script>
</head>
<body>

<a title="" href="/"><img src="/App/templates/assets/logo.svg"></img></a>

<?php
