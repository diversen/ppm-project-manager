<?php

use Diversen\Lang;
use App\AppMain;
use Pebble\Template;

Template::render('templates/header.tpl.php');
Template::render('templates/flash.tpl.php');

?>
<h3 class="sub-menu"><?= Lang::translate('Email sign up') ?></h3>

<form id="signup-form">
    <input type="hidden" name="csrf_token" value="<?= $token ?>" />
    <label for="email"><?= Lang::translate('E-mail') ?></label>
    <input type="text" name="email">

    <label for="password"><?= Lang::translate('Password') ?></label>
    <input type="password" name="password">

    <label for="password"><?= Lang::translate('Repeat password') ?></label>
    <input type="password" name="password_2">

    <button id="submit" class="btn btn-primary"><?= Lang::translate('Send') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script type="module" nonce="<?=AppMain::getNonce()?>">
    
    import {Pebble} from '/js/pebble.js?v=<?=AppMain::VERSION?>';

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
        }
        spinner.classList.toggle('hidden');
    });
</script>
<?php

Template::render('templates/footer.tpl.php');
