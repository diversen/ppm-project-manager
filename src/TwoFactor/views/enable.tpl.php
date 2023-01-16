<?php

use Diversen\Lang;
use App\AppMain;

?>
<h3 class="sub-menu"><?= Lang::translate('Enable two factor authentication') ?></h3>

<div class="text">
    <p><?= Lang::translate('You will need a two factor app on your mobile phone') ?></p>
    <p><?= Lang::translate('Download a two factor app in your app store') ?></p>
    <p><?= Lang::translate('1. Scan the QR code to get started') ?></p>

</div>
<?=$qr_image?>
<form id="two-factor-form">
    <label for="code"><?= Lang::translate('2. Enter code as seen on your phone') ?></label>
    <input id="code" type="code" type="text" name="code">
    <br>
    <button id="check"><?= Lang::translate('Submit') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<div class="text">
    <p><?= Lang::translate('You can use the following apps and many others') ?></p>
    <ul>
        <li>Google Authenticator.</li>
        <li>Lastpass.</li>
        <li>Microsoft Authenticator.</li>
        <li>Authy by Twilio.</li>
        <li>2FA Authenticator.</li>
        <li>Duo Mobile.</li>
        <li>Aegis Authenticator.</li>
    </ul>
    </p>
</div>


<script type="module" nonce="<?= AppMain::getNonce() ?>">
    import { Pebble } from '/js/pebble.js?v=<?= AppMain::VERSION ?>';

    let spinner = document.querySelector('.loadingspinner');
    let submitElem = document.getElementById('check');
    submitElem.addEventListener('click', async function(event) {

        event.preventDefault();

        spinner.classList.toggle('hidden');
        let formData = new FormData(document.getElementById('two-factor-form'));
        let res;

        try {
            res = await Pebble.asyncPost('/twofactor/put', formData);
            if (res.error) {
                Pebble.setFlashMessage(res.message, 'error');
            } else {
                Pebble.setFlashMessage(res.message, 'success');
            }
        } catch (e) {
            await Pebble.asyncPostError('/error/log', e.stack);
        } finally {
            spinner.classList.toggle('hidden');
        }


    });
</script>