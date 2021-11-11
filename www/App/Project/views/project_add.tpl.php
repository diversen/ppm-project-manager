<?php

use \Diversen\Lang;
use \Pebble\CSRF;

$csrf_token = (new CSRF())->getToken();

require 'App/templates/header.tpl.php';

?>
<h3 class="sub-menu"><?= Lang::translate('Add project') ?></h3>

<form id="project_add" name="project_add" method="post">
    <label for="title"><?= Lang::translate('Title') ?> *</label>
    <input id="title" type="text" name="title" placeholder="<?= Lang::translate('Enter title') ?>" value="" class="input-large">
    <label for="note"><?= Lang::translate('Note') ?></label>
    <textarea name="note" placeholder="<?= Lang::translate('Add an optional project note') ?>"></textarea>
    <button id="project_submit" type="submit" name="submit" value="submit"><?= Lang::translate('Add') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script type="module">
    import {
        Pebble
    } from '/App/js/pebble.js';

    const title = document.getElementById('title');
    title.focus();

    const return_to = Pebble.getQueryVariable('return_to');
    const spinner = document.querySelector('.loadingspinner');

    var elem = document.getElementById('project_submit');
    elem.addEventListener('click', async function(e) {
        e.preventDefault();

        spinner.classList.toggle('hidden');

        const form = document.getElementById('project_add');
        const data = new FormData(form);

        try {
            const res = await Pebble.asyncPost('/project/post', data);
            spinner.classList.toggle('hidden');
            if (res.error === false) {
                if (return_to) {
                    window.location.replace(return_to);
                } else {
                    window.location.replace(res.project_redirect);
                }
            } else {
                Pebble.setFlashMessage(res.error, 'error');
            }
        } catch (e) {
            spinner.classList.toggle('hidden');
            Pebble.asyncPostError('/error/log', e.stack)
        }
    })
</script>

<?php

require 'App/templates/footer.tpl.php';
