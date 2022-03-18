<?php

use Diversen\Lang;

require 'App/templates/header.tpl.php';

?>
<h3 class="sub-menu"><?=Lang::translate('Enable two factor authentication')?></h3>

<p><?= Lang::translate('You will need a two factor app on your mobile phone')?></p>
<p><?= Lang::translate('Download a two factor app in your app store')?></p>
<p><?=Lang::translate('1. Scan the QR code to get started')?></p>
<img src="<?=$qr_image?>">

<form id="two-factor-form">
    <label for="code"><?= Lang::translate('2. Enter code as seen on your phone') ?></label>
    <input type="code" class="form-control" type="text" name="code">
    <br />
    <button id="check"><?= Lang::translate('Submit') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<p><?=Lang::translate('You can use the following apps and many others')?></p>
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


<script type="module">
    
    import {Pebble} from '/App/js/pebble.js';

    let spinner = document.querySelector('.loadingspinner');
    let submitElem = document.getElementById('check');
    submitElem.addEventListener('click', async function(event) {

        event.preventDefault();

        spinner.classList.toggle('hidden');
        let formData = new FormData(document.getElementById('two-factor-form'));
        let res;

        try {
            res = await Pebble.asyncPost('/2fa/put', formData);
            console.log(res)
            if (res.error) {
                Pebble.setFlashMessage(res.error, 'error');
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
<?php

require 'App/templates/footer.tpl.php';
