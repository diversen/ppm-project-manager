<?php declare(strict_types=1);

use Diversen\Lang;

$has_error = $error ?? null;

if (!$has_error): ?>

<h3 class="sub-menu"><?= Lang::translate('Create new password')?></h3>
<form id="signup-form" method="post" action="#">

    <input type="hidden" name="csrf_token" value="<?=$token?>" />
    <label for="password"><?=Lang::translate('New password')?></label>
    <input id="password" type="password" name="password">

    <label for="password_2"><?=Lang::translate('Repeat new password')?></label>
    <input id="password_2" type="password" name="password_2">

    <button id="submit" class="btn btn-primary"><?=Lang::translate('Send')?></button>
    <div class="loadingspinner hidden"></div>
</form>
<?php

endif;
