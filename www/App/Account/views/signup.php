<?php

$bd = \Pebble\Config::get('App.basedir');

require 'App/templates/header.tpl.php';
require 'App/templates/flash.tpl.php';

use Diversen\Lang;

?>
<h3 class="sub-menu"><?= Lang::translate('Signup') ?></h3>

<form id="signup-form">
    <input type="hidden" name="csrf_token" value="<?= $token ?>" />
    <label for="email"><?= Lang::translate('E-mail') ?></label>
    <input type="text" name="email">

    <label for="password"><?= Lang::translate('Password') ?></label>
    <input type="password" name="password">

    <label for="password"><?= Lang::translate('Repeat password') ?></label>
    <input type="password" name="password_2">


    <img title="<?= Lang::translate('Click to get a new image') ?>" src="/account/captcha" onclick="this.src='/account/captcha?'+Math.random()" style="cursor: pointer;">
    <br />
    <label for="captcha"><?= Lang::translate('Enter above image text (click to get a new image). Case of the text does not matter') ?>:</label>


    <input class="form-control" autocomplete="off" type="text" name="captcha">

    <button id="submit" class="btn btn-primary"><?= Lang::translate('Send') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script type="module">
    
    import {Pebble} from '/App/js/pebble.js';

    var spinner = document.querySelector('.loadingspinner');

    document.getElementById('submit').addEventListener("click", async function(e) {

        e.preventDefault();

        spinner.classList.toggle('hidden');

        var form = document.getElementById('signup-form');
        var data = new FormData(form);

        let res;
        try {
            res = await Pebble.asyncPost('/account/post_signup', data);

            if (res.error === false) {

                window.location.replace(res.redirect);

            } else {
                Pebble.setFlashMessage(res.message, 'error');
            }
            console.log(res);
        } catch (e) {
            console.log(e)
        }
        spinner.classList.toggle('hidden');
    });
</script>
<?php

require 'App/templates/footer.tpl.php';
