<?php

use Diversen\Lang;
use App\AppMain;
use App\AppUtils;

?>
<h3 class="sub-menu"><?= Lang::translate('Email sign up') ?></h3>

<form id="signup-form">
    
    <?=(new AppUtils())->getCSRF()->getCSRFFormField()?>
    
    <label for="email"><?= Lang::translate('E-mail') ?></label>
    <input id="email" type="text" name="email">

    <label for="password"><?= Lang::translate('Password') ?></label>
    <input id="password" type="password" name="password">

    <label for="password_2"><?= Lang::translate('Repeat password') ?></label>
    <input id="password_2" type="password" name="password_2">

    <img id="captcha" title="<?= Lang::translate('Click to get a new image') ?>" src="/account/captcha">
    <br />

    <label for="captcha"><?= Lang::translate('Enter above image text (click to get a new image). Case of the text does not matter') ?>:</label>
    <input id="captcha" autocomplete="off" type="text" name="captcha">

    <button id="submit" class="btn btn-primary"><?= Lang::translate('Send') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script type="module" nonce="<?=(new AppUtils())->getCSP()->getNonce();?>">
    
    import {Pebble} from '/js/pebble.js?v=<?=AppMain::VERSION?>';

    document.getElementById('captcha').addEventListener('click', function() {
        this.src = '/account/captcha?' + Math.random();
    });

    const spinner = document.querySelector('.loadingspinner');

    document.getElementById('submit').addEventListener("click", async function(e) {

        e.preventDefault();

        spinner.classList.toggle('hidden');

        const form = document.getElementById('signup-form');
        const data = new FormData(form);

        try {

            const res = await Pebble.asyncPost('/account/post_signup', data);
            if (res.error === false) {
                Pebble.redirect(res.redirect);
            } else {
                
                Pebble.setFlashMessage(res.message, 'error');
            }

        } catch (e) {
            await Pebble.asyncPostError('/error/log', e.stack);
        } finally {
            spinner.classList.toggle('hidden');
        }
    });
</script>
