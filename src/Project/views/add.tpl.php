<?php

use Diversen\Lang;
use Pebble\CSRF;
use App\AppMAin;

$csrf_token = (new CSRF())->getToken();

?>
<h3 class="sub-menu"><?= Lang::translate('Add project') ?></h3>

<form id="project_add" name="project_add" method="post">
    <label for="title"><?= Lang::translate('Title') ?> *</label>
    <input id="title" type="text" name="title" placeholder="<?= Lang::translate('Enter title') ?>" value="" class="input-large">
    <label for="note"><?= Lang::translate('Note') ?></label>
    <textarea id="note" name="note" placeholder="<?= Lang::translate('Add an optional project note') ?>"></textarea>
    <button id="project_submit" type="submit" name="submit" value="submit"><?= Lang::translate('Add') ?></button>
    <div class="loadingspinner hidden"></div>
</form>


<script type="module" nonce="<?=AppMain::getNonce()?>">
    import {
        Pebble
    } from '/js/pebble.js?v=<?=AppMain::VERSION?>';

    const title = document.getElementById('title');
    title.focus();

    const returnTo = Pebble.getQueryVariable('return_to');
    const spinner = document.querySelector('.loadingspinner');

    var elem = document.getElementById('project_submit');
    elem.addEventListener('click', async function(e) {
        e.preventDefault();

        spinner.classList.toggle('hidden');

        const form = document.getElementById('project_add');
        const data = new FormData(form);

        try {
            const res = await Pebble.asyncPost('/project/post', data);
            
            if (res.error === false) {
                if (returnTo) {
                    Pebble.redirect(returnTo);
                } else {
                    Pebble.redirect(res.redirect);
                }
            } else {
                Pebble.setFlashMessage(res.message, 'error');
            }
        } catch (e) {
            Pebble.asyncPostError('/error/log', e.stack)
        } finally {
            spinner.classList.toggle('hidden');
        }
    })
</script>