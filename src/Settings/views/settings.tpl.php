<?php

declare(strict_types=1);

use Diversen\Lang;
use App\AppMain;
use Pebble\HTML\Tag;

function select($name, $select_options, $selected = null)
{
    $str = "<select id='$name' name='$name'>";
    foreach ($select_options as $key => $option) {
        $checked = '';
        if ($option == $selected) {
            $checked = ' selected ';
        }
        $str .= "<option value='$option' $checked>$option</option>";
    }
    $str .= "</select>";
    return $str;
}

function is_checked($value)
{
    if ($value) {
        return ' checked ';
    }

    return '';
}

function get_settings_links()
{
    $config = (new AppMain())->getConfig();
    $links = [];
    if ($config->get('TwoFactor.enabled')) {
        $links[] = Tag::getTag('a', Lang::translate('Two factor authentication'), ['href' => '/twofactor/enable']);
    }
    if ($config->get('Notification.enabled')) {
        $links[] = Tag::getTag('a', Lang::translate('Notifications'), ['href' => '/notification']);
    }

    return implode('', $links);
}

$timezones = timezone_identifiers_list();
$languages = (new AppMain())->getConfig()->get('Language.enabled');

?>

<h3 class="sub-menu"><?= Lang::translate('Settings') ?></h3>
<div class="action-links">
    <?=get_settings_links()?>
</div>
<div class="clear"></div>
<form name="settings" id="seetings" method="post">

    <label for="name"><?= Lang::translate('Your name') ?></label>
    <input id="name" type="text" name="name" value="<?= $user_settings['name'] ?? '' ?>" placeholder="<?= Lang::translate('Your name') ?>">

    <label for="bio"><?= Lang::translate('Bio') ?></label>
    <textarea id="bio" name="bio" placeholder="<?= Lang::translate('Add a bio') ?>"><?= $user_settings['bio'] ?? '' ?></textarea>

    <label for="timezone"><?= Lang::translate('Select your timezone') ?></label>
    <?= select('timezone', $timezones, $user_settings['timezone'] ?? (new AppMain())->getConfig()->get('App.timezone')); ?>

    <label for="language"><?= Lang::translate('Select language') ?></label>
    <?= select('language', $languages, $user_settings['language'] ?? (new AppMain())->getConfig()->get('Language.default')); ?>

    <input id="theme_dark_mode" type="checkbox" name="theme_dark_mode" value="1" <?= is_checked($user_settings['theme_dark_mode'] ?? null) ?>>
    <label for="theme_dark_mode"><?= Lang::translate('Theme. Use dark mode') ?></label><br>

    <button id="settings_submit" type="submit" name="submit" class="update_settings"><?= Lang::translate('Update') ?></button>
    <div class="loadingspinner hidden"></div>
</form>
<script type="module" nonce="<?=AppMain::getNonce()?>">
    
    import {Pebble} from '/js/pebble.js?v=<?=AppMain::VERSION?>';
    import {addMultipleEventListener} from '/js/event.js?v=<?=AppMain::VERSION?>'

    document.getElementById('name').focus();

    let spinner = document.querySelector('.loadingspinner');
    let submitElem = document.getElementById('settings_submit');
    addMultipleEventListener(submitElem, ['click', 'touchstart'], async function(event) {

        event.preventDefault();
        spinner.classList.toggle('hidden');

        let formData = new FormData(document.getElementById('seetings'));
        if (!formData.get('theme_dark_mode')) {
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
        } catch (e) {
            await Pebble.asyncPostError('/error/log', e.stack);
        } finally {
            spinner.classList.toggle('hidden');
        }

        
    });
</script>
