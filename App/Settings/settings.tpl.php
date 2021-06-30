<?php declare (strict_types = 1);

use \Diversen\Lang;

require 'App/templates/header.tpl.php'; 

function select($name, $select_options, $selected = null) {

    $str = "<select name='$name'>";
    foreach($select_options as $key => $option) {
        $checked = '';
        if ($option == $selected){
            $checked = ' selected = "true"';
        }
        $str.= "<option value='$option' $checked>$option</option>";
    }
    $str.= "</select>";
    return $str;
}

function is_checked($value) {
    if ($value) {
        return ' checked ';
    } 

    return '';
}

$timezones = timezone_identifiers_list();
$languages = \Pebble\Config::get('Language.enabled');

?>



<h3 class="sub-menu"><?=Lang::translate('Settings')?></h3>

<form name="settings" id="seetings" method="post">

<label for="company"><?=Lang::translate('Organization')?></label>
<input type="text" name="company" value="<?=$company?>" placeholder="<?=Lang::translate('Organization')?>">

<label for="name"><?=Lang::translate('Your name')?></label>
<input type="text" name="name" value="<?=$name?>" placeholder="<?=Lang::translate('Your name')?>">

<label for="phone"><?=Lang::translate('Phone number')?></label>
<input type="text" name="phone" value="<?=$phone?>" placeholder="Phone number">

<label for="bio"><?=Lang::translate('Bio')?></label>
<textarea name="bio" placeholder="<?=Lang::translate('Add a bio')?>"><?=$bio?></textarea>

<label for="timezone"><?=Lang::translate('Select your timezone')?></label>
<?=select('timezone', $timezones, $timezone);?>

<label for="language"><?=Lang::translate('Select language')?></label>
<?=select('language', $languages, $language);?>

<input type="checkbox" name="theme_dark_mode" value="1" <?=is_checked($theme_dark_mode)?> >
<label for="theme"><?=Lang::translate('Theme. Use dark mode')?></label><br />

<button id="settings_submit" type="submit" name="submit" class="update_settings"><?=Lang::translate('Update')?></button>
<div class="loadingspinner hidden"></div>
</form>
<script>

    let spinner = document.querySelector('.loadingspinner');
    let submitElem = document.getElementById('settings_submit');
    submitElem.addEventListener('click', async function(event) {

        event.preventDefault();

        let formData = new FormData(document.getElementById('seetings'));

        // Unchecked gets a value of 0
        if (!formData.has('theme_dark_mode')){
            formData.set('theme_dark_mode', '0');
        }

        let res;

        try {
            res = await Pebble.asyncPost('/settings/put/', formData);
            if (res.error === false) {
                location.reload();
            } else {
                Pebble.setFlashMessage(res.error, 'error');
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
