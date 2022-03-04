<?php

use Diversen\Lang;
use App\AppMain;

$auth = (new AppMain())->getAuth();

?>
<div class="app-menu">
<a href="/account"><?=Lang::translate('Email login')?></a>
<a href="/account/signup"><?=Lang::translate('Email signup')?></a>
<a href="/account/recover"><?=Lang::translate('Lost password')?></a>
</div>