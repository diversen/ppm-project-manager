<?php

declare(strict_types=1);

use Diversen\Lang;
use App\AppMain;

require 'templates/header.tpl.php';

?>

<h3 class="sub-menu"><?=$title?></h3>

<p><?=Lang::translate('By signing in you agree to the following terms of service, privacy policy, and disclaimer')?></p>
<p>
    <a href="/terms/terms-of-service"><?=Lang::translate('Terms of service')?></a> | 
    <a href="/terms/privacy-policy"><?=Lang::translate('Privacy policy')?></a> |
    <a href="/terms/disclaimer"><?=Lang::translate('Disclaimer')?></a>
</p>

<?php

if ($google_auth_url): ?>

<div class="row">
    <p><a href="<?=$google_auth_url?>"><img src="/assets/google-signin.png" /></a></p>
</div>

<?php

endif;

?>

<form id="login-form">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
    
    <label for="email"><?= Lang::translate('E-mail') ?></label>
    <input type="email" type="text" name="email">

    <label for="password"><?= Lang::translate('Password') ?></label>
    <input type="password" name="password">

    <label for="keep_login">
        <?= Lang::translate('Keep me signed in') ?>
    </label>

    <input type="checkbox" value="1" id="keep_login" name="keep_login" checked="checked">
        
    <br />
    <button id="login"><?= Lang::translate('Send') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script type="module" nonce="<?=AppMain::getNonce()?>">

    import {Pebble} from '/js/pebble.js?v=<?=AppMain::VERSION?>';
    
    var spinner = document.querySelector('.loadingspinner');

    document.getElementById('login').addEventListener("click", async function(e) {

        e.preventDefault();

        spinner.classList.toggle('hidden');

        const form = document.getElementById('login-form');
        const data = new FormData(form);

        try {
            const res = await Pebble.asyncPost('/account/post_login', data);
            
            if (res.error === false) {
                Pebble.redirect(res.redirect);
            } else {
                Pebble.setFlashMessage(res.message, 'error');
            }

        } catch (e) {
            await Pebble.asyncPostError('/error/log', e.stack);
        }

        spinner.classList.toggle('hidden');
    });

</script>

<?php

require 'templates/footer.tpl.php';
