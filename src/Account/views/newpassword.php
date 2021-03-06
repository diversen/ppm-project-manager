<?php declare(strict_types=1);

use Diversen\Lang;
use Pebble\Template;

Template::render('templates/header.tpl.php');

$has_error = $error ?? null;

if (!$has_error): ?>

<h3 class="sub-menu"><?= Lang::translate('Create new password')?></h3>
<form id="signup-form" method="post" action="#">

    <input type="hidden" name="csrf_token" value="<?=$token?>" />
    <label for="password"><?=Lang::translate('New password')?></label>
    <input type="password" name="password">

    <label for="password"><?=Lang::translate('Repeat new password')?></label>
    <input type="password" name="password_2">

    <button id="submit" class="btn btn-primary"><?=Lang::translate('Send')?></button>
    <div class="loadingspinner hidden"></div>
</form>
<?php

endif;


Template::render('templates/footer.tpl.php');
