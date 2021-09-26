<?php

use Diversen\Lang;
use Pebble\Config;
use Pebble\Auth;

$is_authenticated = Auth::getInstance()->isAuthenticated();
if ($is_authenticated) {
    $home_url = '/overview';
} else {
    $home_url = '/';
}

?>
<a title="<?=Config::get('App.site_name')?>" href="<?=$home_url?>">
    <img src="/App/templates/assets/logo.svg" title="<?=Config::get('App.site_name')?>" width="184" height="74"></img>
</a>

<div class="app-menu">
<?php

if (!$is_authenticated) {?>
<a href="/account/signin" data-path="/account/signin"><?=Lang::translate('Login')?></a>
<a href="/account/signup" data-path="/account/signup"><?=Lang::translate('Email signup')?></a>
<a href="/account/recover" data-path="/account/recover"><?=Lang::translate('Lost password')?></a>
<?php } else {?>
<a href="/overview" data-path="/overview"><?=Lang::translate('Home')?></a>
<a href="/project" data-path="/project"><?=Lang::translate('Projects')?></a>
<a href="/settings" data-path="/settings"><?=Lang::translate('Settings')?></a>
<a href="/account/signout" data-path="/account/signout"><?=Lang::translate('Sign out')?></a>
<a id="timer_toggle" title="<?=Lang::translate('Toggle timer')?>" href="#">&#128337; <?=Lang::translate('Timer')?></a>
</div>
<?php

}

if ($is_authenticated) {
    require 'App/templates/timer.tpl.php';
} 
