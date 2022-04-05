<?php

use Diversen\Lang;
use App\AppMain;

require 'templates/header.tpl.php';


?>
<h3 class="sub-menu"><?=Lang::translate('Verify login using two factor authentication')?></h3>


<form id="two-factor-form">
    <label for="code"><?= Lang::translate('1. Enter code as seen on your phone') ?></label>
    <input type="code" type="text" name="code">
    <br />
    <button id="check"><?= Lang::translate('Submit') ?></button>
    <div class="loadingspinner hidden"></div>
</form>



<script type="module" nonce="<?=AppMain::getNonce()?>">
    
    import {Pebble} from '/js/pebble.js';

    let spinner = document.querySelector('.loadingspinner');
    let submitElem = document.getElementById('check');
    submitElem.addEventListener('click', async function(event) {

        event.preventDefault();

        spinner.classList.toggle('hidden');
        let formData = new FormData(document.getElementById('two-factor-form'));
        let res;

        try {
            res = await Pebble.asyncPost('/2fa/verify/post', formData);
            
            if (res.error) {
                Pebble.setFlashMessage(res.error, 'error');
            } else {
                // Pebble.setFlashMessage(res.message, 'success');
                window.location.replace(res.redirect);
            }
        } catch (e) {
            await Pebble.asyncPostError('/error/log', e.stack);
        } finally {
            spinner.classList.toggle('hidden');
        }

        
    });
</script>
<?php

require 'templates/footer.tpl.php';
