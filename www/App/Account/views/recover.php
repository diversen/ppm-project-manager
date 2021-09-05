<?php declare (strict_types = 1);

require 'App/templates/header.tpl.php';

use Diversen\Lang;

?>

<h3 class="sub-menu"><?=$title?></h3>

<form id="signup-form">

    <input type="hidden" name="csrf_token" value="<?=$token?>" />
    <label for="email"><?=Lang::translate('E-mail')?></label>
    <input class="form-control" type="text" name="email">

    <img title="<?=Lang::translate('Click to get a new image')?>" src="/account/captcha" onclick="this.src='/account/captcha?'+Math.random()" style="cursor: pointer;">
    <br/>


    <label for="captcha"><?=Lang::translate('Enter above image text (click to get a new image)')?>:</label>
    <input class="form-control" autocomplete="off" type="text" name="captcha">


    <button id="submit" class="btn btn-primary"><?=Lang::translate('Send')?></button>
    <div class="loadingspinner hidden"></div>
</form>

<script>

var spinner = document.querySelector('.loadingspinner');

document.getElementById('submit').addEventListener("click", async function(e) {


    e.preventDefault();

    spinner.classList.toggle('hidden');

    var form = document.getElementById('signup-form');
    var data = new FormData(form);

    let res;
    try {
        res = await Pebble.asyncPost('/account/post_recover', data);
        
        if (res.error === false) {
            window.location.replace('/account');
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