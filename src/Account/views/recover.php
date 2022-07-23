<?php

declare(strict_types=1);

use Diversen\Lang;
use App\AppMain;
use Pebble\Template;

Template::render('templates/header.tpl.php');

?>

<h3 class="sub-menu"><?= Lang::translate('Forgotten password') ?></h3>

<form id="signup-form">

    <input type="hidden" name="csrf_token" value="<?= $token ?>" />
    <label for="email"><?= Lang::translate('E-mail') ?></label>
    <input  type="text" name="email">

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

            const res = await Pebble.asyncPost('/account/post_recover', data);
            if (res.error === false) {
                Pebble.redirect('/account/signin');
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
