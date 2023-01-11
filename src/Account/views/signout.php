<?php

use Diversen\Lang;

?>
<h3 class="sub-menu"><?=Lang::translate('Sign out')?></h3>
<div class="action-links">
    <a href="/account/logout"><?=Lang::translate('Sign out')?></a>
    <a href="/account/logout?all_devices=1"><?=Lang::translate('Sign out of all devices')?></a>
</div>