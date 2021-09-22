<?php 

declare (strict_types = 1);

require 'App/templates/header.tpl.php'; 

use Diversen\Lang;

?>

<h3 class="sub-menu"><?=$title?></h3>

<p><?=Lang::translate('By signing in you agree to the following terms of service, privacy policy, and disclaimer')?></p>
<p>
    <a href="/terms/terms-of-service"><?=Lang::translate('Terms of service')?></a> | 
    <a href="/terms/privacy-policy"><?=Lang::translate('Privacy policy')?></a> |
    <a href="/terms/disclaimer"><?=Lang::translate('Disclaimer')?></a>
</p>

<?php

if ($google_auth_url): ?>

<div class="row">
    <p><a href="<?=$google_auth_url?>"><img src="/App/templates/assets/google-signin.png" /></a></p>
</div>

<?php

endif;

?>

<form id="login-form">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" />
    
    <label for="email"><?= Lang::translate('E-mail') ?></label>
    <input type="email" class="form-control" type="text" name="email">

    <label for="password"><?= Lang::translate('Password') ?></label>
    <input type="password" class="form-control" name="password">

    <label class="form-check-label" for="keep_login">
        <?= Lang::translate('Keep me signed in') ?>
    </label>

    <input type="checkbox" value="1" id="keep_login" name="keep_login" checked="checked">
        
    <br />
    <button id="login"><?= Lang::translate('Send') ?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script type="module">

    import {Pebble} from '/App/js/pebble.js';
    
    var spinner = document.querySelector('.loadingspinner');

    document.getElementById('login').addEventListener("click", async function(e) {


        e.preventDefault();

        spinner.classList.toggle('hidden');

        var form = document.getElementById('login-form');
        var data = new FormData(form);

        let res;
        try {
            res = await Pebble.asyncPost('/account/post_login', data);
            
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