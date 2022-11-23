<?php

declare(strict_types=1);

use Diversen\Lang;
use App\AppMain;

?>

<h3 class="sub-menu"><?= Lang::translate('Forgotten password') ?></h3>

<form id="signup-form">

    <input type="hidden" name="csrf_token" value="<?= $token ?>" />
    <label for="email"><?= Lang::translate('E-mail') ?></label>
    <input type="text" name="email">

    <img id="captcha" title="<?= Lang::translate('Click to get a new image') ?>" src="/account/captcha">
    <br />

    <label for="captcha"><?= Lang::translate('Enter above image text (click to get a new image). Case of the text does not matter') ?>:</label>
    <input autocomplete="off" type="text" name="captcha">

    <button id="submit" class="btn btn-primary"><?= Lang::translate('Send') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script type="module" nonce="<?= AppMain::getNonce() ?>">
    import { Pebble } from '/js/pebble.js?v=<?= AppMain::VERSION ?>';

    document.getElementById('captcha').addEventListener('click', function() {
        this.src = '/account/captcha?' + Math.random();
    });

    Pebble.addPostEventListener({
        'route': '/account/post_recover',
        'eventElem': '#submit',
        'formElem': '#signup-form',
        'loaderElem': '.loadingspinner',
        'onSuccessCallback': function(response) {
            if (response.error === false) {
                Pebble.redirect('/account/signin');
            } else {
                Pebble.setFlashMessage(response.message, 'error');
            }
        }
    });

</script>