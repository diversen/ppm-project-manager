<?php

use App\AppMain;

$bd = (new AppMain())->getConfig()->get('App.basedir');

require 'templates/header.tpl.php';
require 'templates/flash.tpl.php';

use Diversen\Lang;

?>

<h3 class="sub-menu">
    <a href="/project"><?=Lang::translate('Projects')?></a><?=SUB_MENU_SEP?>
    <a href="/project/view/<?=$project['id']?>"><?=$project['title']?></a><?=SUB_MENU_SEP?>
    <?=Lang::translate('Add task')?>
</h3>

<form id="task_add" name="task_add" method="post">
    <label for="title"><?=Lang::translate('Title')?> *</label>
    <input id="title" class="input-large" type="text" name="title" placeholder="<?=Lang::translate('Enter title')?>"
        value="">


    <label for="note"><?=Lang::translate('Add note')?></label>
    <textarea name="note" placeholder="<?=Lang::translate('Add an optional task note')?>"></textarea>

    <label for="priority"><?=Lang::translate('Priority')?></label>
    <select name="priority">
        <option value="4"><?=Lang::translate('Urgent')?></option>
        <option value="3"><?=Lang::translate('High')?></option>
        <option value="2" selected><?=Lang::translate('Normal')?></option>
        <option value="1"><?=Lang::translate('Minor')?></option>
        <option value="0"><?=Lang::translate('Low')?></option>
    </select>

    <label for="begin_data"><?=Lang::translate('Task begin date')?> *</label>
    <input id="begin_date" type="date" name="begin_date" placeholder="<?=Lang::translate('Pick begin date')?>"
        value="<?=$task['begin_date']?>">

    <label for="end_data"><?=Lang::translate('Task end date')?></label>
    <input id="end_date" type="date" name="end_date" placeholder="<?=Lang::translate('Pick end date')?>"
        value="<?=$task['end_date']?>">

    <input id="project_id" type="hidden" name="project_id" value="<?=$project['id']?>">
    <button id="task_submit" type="submit" name="submit" value="submit"><?=Lang::translate('Submit')?></button>
    <button id="task_add_another" type="submit" name="submit" value="submit"><?=Lang::translate('Submit and stay')?></button>
    <div class="loadingspinner hidden"></div>

</form>
<script type="module" nonce="<?=AppMain::getNonce()?>">

    import {Pebble} from '/js/pebble.js?v=<?=AppMain::VERSION?>';
    import {addMultipleEventListener} from '/js/event.js?v=<?=AppMain::VERSION?>'

    const title = document.getElementById('title');
    title.focus();

    const spinner = document.querySelector('.loadingspinner');

    const task_submit = document.getElementById('task_submit');  
    addMultipleEventListener(task_submit, ['click', 'touchstart'], async function (e) {
        
        e.preventDefault();

        const form = document.getElementById('task_add');
        const data = new FormData(form);
        data.append('status', '1');

        const return_to = Pebble.getQueryVariable('return_to');

        try {
            const res = await Pebble.asyncPost('/task/post', data);
            spinner.classList.toggle('hidden');

            if (res.error === false) {
                if (return_to) {
                    Pebble.redirect(return_to);
                } else {
                    Pebble.redirect(res.project_redirect);
                }
            } else {
                Pebble.setFlashMessage(res.error, 'error');
            }
        } catch (e) {
            await Pebble.asyncPostError('/error/log', e.stack);
        } finally {
            spinner.classList.toggle('hidden');
        }
    });

    const task_add_another = document.getElementById('task_add_another');
    addMultipleEventListener(task_add_another, ['click', 'touchstart'], async function (e) {

        spinner.classList.toggle('hidden');

        var form = document.getElementById('task_add');
        var data = new FormData(form);
        data.append('status', '1');

        let res;
        let return_to = Pebble.getQueryVariable('return_to');

        try {
            res = await Pebble.asyncPost('/task/post', data);
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

<?php

require 'templates/footer.tpl.php';
