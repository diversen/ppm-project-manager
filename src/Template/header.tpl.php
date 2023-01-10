<?php

declare(strict_types=1);

use App\Template\TemplateUtils;
use App\Template\TemplateMenu;
use App\AppMain;

$template_utils = new TemplateUtils();
$template_menu = new TemplateMenu();
 
$use_dark_mode = $template_utils->useDarkMode();

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

    if ($use_dark_mode) : ?>
        <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/dark.min.css?v=<?= AppMain::VERSION ?>">
    <?php else : ?>
        <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/light.min.css?v=<?= AppMain::VERSION ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="/css/default.css?v=<?= AppMain::VERSION ?>">
    <link rel="stylesheet" href="/css/cookie-consent.css?v=<?= AppMain::VERSION ?>">
    <link rel="stylesheet" href="/css/modification.css?v=<?= AppMain::VERSION ?>">

    <link rel="apple-touch-icon" sizes="180x180" href="/favicon_io/apple-touch-icon.png?v=<?= AppMain::VERSION ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon_io/favicon-32x32.png?v=<?= AppMain::VERSION ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon_io/favicon-16x16.png?v=<?= AppMain::VERSION ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css?v=<?= AppMain::VERSION ?>" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="manifest" href="/assets/manifest.json?v=<?= AppMain::VERSION ?>">

    <?php

    if (file_exists('../src/Template/parts/head_scripts.tpl.php')) {
        require 'Template/parts/head_scripts.tpl.php';
    }

    ?>
</head>
<body>
<?php

    if (file_exists('../src/Template/parts/body_begin.tpl.php')) {
        require 'Template/parts/body_begin.tpl.php';
    }

    ?>
    <div class="page">

        <?php

        $template_utils->renderLogo();

        $template_menu->renderMainMenu();
        
        $template_utils->renderFlashMessages();
