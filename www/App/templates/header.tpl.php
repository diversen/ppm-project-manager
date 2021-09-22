<?php declare (strict_types = 1);

use \App\Settings\SettingsModel;
use \Pebble\Auth;

$settings = new SettingsModel;
$auth = Auth::getInstance();
if (!$auth->isAuthenticated() && isset($_COOKIE['theme_dark_mode'])) {
    $use_theme_dark_mode = $_COOKIE['theme_dark_mode'];
} else {
    $profile = $settings->getUserSetting($auth->getAuthId(), 'profile');
    $use_theme_dark_mode = $profile['theme_dark_mode'] ?? null;
}

if (!isset($title)) {
    $title = 'PPM';
}

if (!isset($description)) {
    $description = $title;
}

require 'App/templates/helpers.php';

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
    <link rel="stylesheet" href="/App/templates/css/water/dark.min.css">
    <?php else: ?>
    <link rel="stylesheet" href="/App/templates/css/water/light.min.css">
    <?php endif; ?>

    <link rel="manifest" href="/App/templates/assets/manifest.json">
    <link rel="stylesheet" href="/App/templates/css/default.css?v=3">
    <link rel="icon" sizes="192x192" href="/App/templates/assets/ppm-logo-192x192.png">

</head>
<body>

<?php

require 'App/templates/main_menu.tpl.php';

require 'App/templates/flash.tpl.php';