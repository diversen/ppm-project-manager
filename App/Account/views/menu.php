<?php

use Pebble\Auth;
use Diversen\Lang;

$auth = new Auth();


?>
<div class="project-menus">

<a href="/account"><?=Lang::translate('Email login')?></a>
<a href="/account/signup"><?=Lang::translate('Email signup')?></a>
<a href="/account/recover"><?=Lang::translate('Lost password')?></a>
        

</div>
