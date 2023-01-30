<?php

use Diversen\Lang;
use App\AppMain;

?>
<h3 class="sub-menu"><?=Lang::translate('Verify login using two factor authentication')?></h3>


<form id="two-factor-form">
    <label for="code"><?= Lang::translate('1. Enter code as seen on your phone') ?></label>
    <input id="code" type="code" type="text" name="code">
    <br>
    <button id="check"><?= Lang::translate('Submit') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script type="module" nonce="<?=AppMain::getNonce();?>">
    
    import {Pebble} from '/js/pebble.js?v=<?=AppMain::VERSION?>';

    document.getElementById('code').focus();

    let spinner = document.querySelector('.loadingspinner');
    let submitElem = document.getElementById('check');
    submitElem.addEventListener('click', async function(event) {

        event.preventDefault();
        
        spinner.classList.toggle('hidden');
        let formData = new FormData(document.getElementById('two-factor-form'));
        let res;

        try {
            res = await Pebble.asyncPost('/twofactor/verify/post', formData);
            
            if (res.error) {
                Pebble.setFlashMessage(res.message, 'error');
            } else {
                Pebble.redirect(res.redirect);
            }
        } catch (e) {
            await Pebble.asyncPostError('/error/log', e.stack);
        } finally {
            spinner.classList.toggle('hidden');
        }

        
    });
</script>
