<?php

use Diversen\Lang;
use App\AppMain;

$app_main = new AppMain();
$is_authenticated = $app_main->getAuth()->isAuthenticated();
if ($is_authenticated) {
    $home_url = '/overview';
} else {
    $home_url = '/';
}


?>
<div class="logo">
    <a title="<?= $app_main->getConfig()->get('App.site_name') ?>" href="<?= $home_url ?>">
        <img  
            src="/assets/logo.png?version=<?=AppMain::VERSION?>" 
            title="<?= $app_main->getConfig()->get('App.site_name') ?>"
            width="70" height="70">
        </img>
    </a>
</div>

<div class="app-menu">
    <?php

    if (!$is_authenticated) { ?>
        <a href="/account/signin" data-path="/account/signin"><?= Lang::translate('Sign in') ?></a>
        <a href="/account/signup" data-path="/account/signup"><?= Lang::translate('Email sign up') ?></a>
        <a href="/account/recover" data-path="/account/recover"><?= Lang::translate('Lost password') ?></a>
    <?php } else { ?>
        <a href="/overview" data-path="/overview"><?= Lang::translate('Home') ?></a>
        <a href="/project" data-path="/project"><?= Lang::translate('Projects') ?></a>
        <a href="/settings" data-path="/settings"><?= Lang::translate('Settings') ?></a>
        <a href="/account/signout" data-path="/account/signout"><?= Lang::translate('Sign out') ?></a>
        <a id="timer_toggle" title="<?= Lang::translate('Toggle timer') ?>" href="#">&#128337; <?= Lang::translate('Timer') ?></a>

</div>
<?php

    }

    if ($is_authenticated) {
        require 'templates/timer.tpl.php';
    }
