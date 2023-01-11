<?php declare(strict_types=1);

use Diversen\Lang;
use App\AppMain;

$has_error = $error ?? null;

if (!$has_error): ?>

<h3 class="sub-menu"><?= Lang::translate('Create new password')?></h3>
<form id="newpassword-form" method="post" action="#">

    <?=AppMain::getCSRFFormField()?>
    <input type="hidden" name="key" value="<?=$key?>">
    
    <label for="password"><?=Lang::translate('New password')?></label>
    <input id="password" type="password" name="password">

    <label for="password_2"><?=Lang::translate('Repeat new password')?></label>
    <input id="password_2" type="password" name="password_2">

    <button id="submit" class="btn btn-primary"><?=Lang::translate('Send')?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script type="module" nonce="<?=AppMain::getNonce()?>">
    
    import {Pebble} from '/js/pebble.js?v=<?=AppMain::VERSION?>';
    
    const spinner = document.querySelector('.loadingspinner');

    document.getElementById('submit').addEventListener("click", async function(e) {

        e.preventDefault();

        spinner.classList.toggle('hidden');

        const form = document.getElementById('newpassword-form');
        const data = new FormData(form);

        try {

            const res = await Pebble.asyncPost('/account/post_newpassword', data);
            console.log(res)
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

<?php

endif;
