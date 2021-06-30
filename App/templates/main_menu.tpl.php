<?php

use Diversen\Lang;
use Pebble\Config;
use Pebble\Auth;

if (Auth::getInstance()->isAuthenticated()) {
    $home_url = '/overview';
} else {
    $home_url = '/';
}

?>
<!--<h1><a href="<?=$home_url?>"><?=Config::get('App.site_name')?></a></h1>-->

<a title="<?=Config::get('App.site_name')?>" href="<?=$home_url?>"><img src="/App/templates/assets/logo.svg"></img></a>

<div class="project-menus">
<?php

if (!Auth::getInstance()->isAuthenticated()) {?>
<a href="/account"><?=Lang::translate('Login')?></a>
<a href="/account/signup"><?=Lang::translate('Email signup')?></a>
<a href="/account/recover"><?=Lang::translate('Lost password')?></a>
<?php } else {?>
<a href="/overview"><?=Lang::translate('Home')?></a>
<a href="/project"><?=Lang::translate('Projects')?></a>
<a href="/settings"><?=Lang::translate('Settings')?></a>
<a href="/account/signout"><?=Lang::translate('Sign out')?></a>
<a id="timer_toggle" title="<?=Lang::translate('Toggle timer')?>" href="#">&#128337; <?=Lang::translate('Timer')?></a>
</div>
<?php

}

if ( (new \Pebble\Auth)->isAuthenticated()) {
    require 'App/templates/timer.tpl.php';
} else {
}
