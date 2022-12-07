<?php

declare(strict_types=1);

use App\Settings\SettingsModel;
use App\AppMain;

$settings = new SettingsModel();
$app_main = new AppMain();
$auth = $app_main->getAuth();
$config = $app_main->getConfig();
$analytics_tag = $config->get('Analytics.tag');

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

if (file_exists('../src/templates/utils.php')) {
    require_once "templates/utils.php";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">
    <meta name="theme-color" content="#ffffff">

    <?php

    if ($use_theme_dark_mode) : ?>
        <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/dark.min.css?version=<?= AppMain::VERSION ?>">
    <?php else : ?>
        <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/light.min.css?version=<?= AppMain::VERSION ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="/css/default.css?version=<?= AppMain::VERSION ?>">
    <link rel="stylesheet" href="/css/cookie-consent.css?version=<?= AppMain::VERSION ?>">

    <link rel="apple-touch-icon" sizes="180x180" href="/favicon_io/apple-touch-icon.png?version=<?= AppMain::VERSION ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon_io/favicon-32x32.png?version=<?= AppMain::VERSION ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon_io/favicon-16x16.png?version=<?= AppMain::VERSION ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css?version=<?= AppMain::VERSION ?>" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="manifest" href="/assets/manifest.json?version=<?= AppMain::VERSION ?>">

    <?php

        if (file_exists('../src/templates/parts/head_scripts.php')) {
            require 'templates/parts/head_scripts.php';
        }
    ?>
</head>
<?php

if (file_exists('../src/templates/parts/body_begin.php')) {
    require 'templates/parts/body_begin.php';
}

?>
<body>
    <div class="page">

        <?php

        require 'templates/main_menu.tpl.php';
        require 'templates/flash.tpl.php';
