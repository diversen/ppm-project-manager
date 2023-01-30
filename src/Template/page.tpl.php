<?php

declare(strict_types=1);

use App\Template\TemplateUtils;
use App\Template\Overrides\TemplateMenu;
use App\AppMain;
use Diversen\Lang;

$template_utils = new TemplateUtils();
$template_menu = new TemplateMenu();

$use_dark_mode = $template_utils->useDarkMode();

$app_main = new AppMain();
$data_container = $app_main->getDataContainer();
$head_elements = $data_container->getArrayData('head_elements');

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

    foreach ($head_elements as $element): ?>
        <?=$element ?>
    <?php
    endforeach;

    if ($use_dark_mode) : ?>
        <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/dark.min.css?v=<?= AppMain::VERSION ?>">
    <?php else : ?>
        <link rel="stylesheet" id="js-startup-stylesheet" href="/css/water/light.min.css?v=<?= AppMain::VERSION ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="/css/default.css?v=<?= AppMain::VERSION ?>">
    <link rel="stylesheet" href="/css/modification.css?v=<?= AppMain::VERSION ?>">
    <link rel="stylesheet" href="/css/cookie-consent.css?v=<?= AppMain::VERSION ?>">

    <link rel="apple-touch-icon" sizes="180x180" href="/favicon_io/apple-touch-icon.png?v=<?= AppMain::VERSION ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon_io/favicon-32x32.png?v=<?= AppMain::VERSION ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon_io/favicon-16x16.png?v=<?= AppMain::VERSION ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css?v=<?= AppMain::VERSION ?>" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="manifest" href="/assets/manifest.json?v=<?= AppMain::VERSION ?>">

    <?php

    $body_begin = $template_utils->getTemplatePath()  . '/Overrides/head_scripts.tpl.php';
    if (file_exists($body_begin)) {
        require $body_begin;
    }

    ?>
</head>
<body>
<?php

    $body_begin = $template_utils->getTemplatePath()  . '/Overrides/body_begin.tpl.php';
    if (file_exists($body_begin)) {
        require $body_begin;
    }

    ?>
    <div class="page">

        <?php

        $template_utils->renderLogo();

        $template_menu->renderMainMenu();

        $template_utils->renderFlashMessages();

        

        ?>

        <?=$data_container->getData('content')?>

        <hr>
        <div class="footer">
        </div>
    </div>
    <div id="cookie-consent">
        <p class="block">
            <span><b><?= Lang::translate('Notice') ?></b></span>:
            <?= Lang::translate('This website may use non-essential cookies for statistical usage and improving experience.'); ?> 
            <br>
            <?=Lang::translate('You may accept or reject any non-essential cookies.') ?> 
            <a href="/account/terms/privacy-policy" target="_blank"><?= Lang::translate('Read more') ?></a>.
        </p>
        <p class="block">
            <button id="cookie-accept" class="accept" type="button"><?=Lang::translate('Accept')?></button>
            <button id="cookie-reject" class="reject" type="button"><?=Lang::translate('Reject')?></button>
        </p>
    </div>
</body>
</html>