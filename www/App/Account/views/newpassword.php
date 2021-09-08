<?php declare (strict_types = 1);

require 'App/templates/header.tpl.php';

use Diversen\Lang;

if ($error == '0'): ?>

<h3 class="sub-menu"><?=$title?></h3>
<form id="signup-form" method="post" action="#">

    <input type="hidden" name="csrf_token" value="<?=$token?>" />
    <label for="password"><?=Lang::translate('New password')?></label>
    <input class="form-control" type="password" name="password">

    <label for="password"><?=Lang::translate('Repeat new password')?></label>
    <input class="form-control" type="password" name="password_2">

    <button id="submit" class="btn btn-primary"><?=Lang::translate('Send')?></button>
    <div class="loadingspinner hidden"></div>
</form>
<?php

endif;


require 'App/templates/footer.tpl.php';