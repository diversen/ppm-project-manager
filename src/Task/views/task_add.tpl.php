<?php

use App\AppMain;
use App\Task\TaskModel;
use Diversen\Lang;

?>

<h3 class="sub-menu">
    <?php
    
    if ($project): ?>
<a href="/project"><?=Lang::translate('Projects')?></a><?=SUB_MENU_SEP?>
<a href="/project/view/<?=$project['id']?>"><?=$project['title']?></a><?=SUB_MENU_SEP?>
    <?php endif; ?>
    <?=Lang::translate('Add task')?>
</h3>

<form id="task_form_add" name="task_add" method="post">
    <label for="title"><?=Lang::translate('Title')?> *</label>
    <input id="title" class="input-large" type="text" name="title" placeholder="<?=Lang::translate('Enter title')?>" value="">


    <label for="note"><?=Lang::translate('Add note')?></label>
    <textarea name="note" placeholder="<?=Lang::translate('Add an optional task note')?>"></textarea>
    <?php

    if ($project): ?>
        <input id="project_id" type="hidden" name="project_id" value="<?=$project['id']?>">
    <?php else: ?>
        <label for="project_id"><?=Lang::translate('Project')?> *</label>
        <select name="project_id">
            <?php foreach ($projects as $project): ?>
                <option value="<?=$project['id']?>"><?=$project['title']?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label for="priority"><?=Lang::translate('Priority')?></label>
    <select name="priority">
        <option value="4"><?=Lang::translate('Urgent')?></option>
        <option value="3"><?=Lang::translate('High')?></option>
        <option value="2" selected><?=Lang::translate('Normal')?></option>
        <option value="1"><?=Lang::translate('Minor')?></option>
        <option value="0"><?=Lang::translate('Low')?></option>
    </select>

    <label for="auto_move"><?= Lang::translate('Repeatable task. Will auto-move the task to a new date when the end date of the task is exceeded.') ?></label>
    <select name="auto_move">
        <option value="<?= TaskModel::AUTO_MOVE_NONE ?>"><?= Lang::translate('Deactivated') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_TODAY ?>" selected><?= Lang::translate('Next day') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_ONE_WEEK ?>"><?= Lang::translate('One week') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_FOUR_WEEKS ?>"><?= Lang::translate('Four weeks') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_FIRST_DAY_OF_NEXT_MONTH ?>"><?= Lang::translate('One month. First day') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_LAST_DAY_OF_THIS_MONTH ?>"><?= Lang::translate('One month. Last day') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_FIRST_SAME_DAY_NEXT_MONTH ?>"><?= Lang::translate("One month. First day same day name.") ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_LAST_SAME_DAY_NEXT_MONTH ?>"><?= Lang::translate('One month. Last day same day name') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_CLOSE_TODAY ?>"><?= Lang::translate('Close task after end date') ?></option>
    </select>

    <label for="begin_data"><?=Lang::translate('Task begin date')?> *</label>
    <input id="begin_date" type="date" name="begin_date" placeholder="<?=Lang::translate('Pick begin date')?>"
        value="<?=$task['begin_date']?>">

    <label for="end_data"><?=Lang::translate('Task end date')?></label>
    <input id="end_date" type="date" name="end_date" placeholder="<?=Lang::translate('Pick end date')?>"
        value="<?=$task['end_date']?>">
    
    <button id="task_add" type="submit" name="submit" value="task_add"><?=Lang::translate('Submit')?></button>
    <button id="task_add_another" type="submit" name="submit" value="task_add_another"><?=Lang::translate('Submit and stay')?></button>
    <div class="loadingspinner hidden"></div>

</form>
<script type="module" nonce="<?=AppMain::getNonce()?>">

    import {Pebble} from '/js/pebble.js?v=<?=AppMain::VERSION?>';
    import {addMultipleEventListener} from '/js/event.js?v=<?=AppMain::VERSION?>'

    const title = document.getElementById('title');
    title.focus();

    const spinner = document.querySelector('.loadingspinner');
    const returnTo = Pebble.getQueryVariable('return_to');
    const task_add = document.getElementById('task_add');

    addMultipleEventListener(task_add, ['click', 'touchstart'], async function (e) {
        
        e.preventDefault();

        const form = document.getElementById('task_form_add');
        const data = new FormData(form);
        
        data.append('status', '1');
        data.append('session_flash', true);

        spinner.classList.toggle('hidden');

        try {
            const res = await Pebble.asyncPost('/task/post', data);
            
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
            await Pebble.asyncPostError('/error/log', e.stack);
        } finally {
            spinner.classList.toggle('hidden');
        }
    });

    const task_add_another = document.getElementById('task_add_another');
    addMultipleEventListener(task_add_another, ['click', 'touchstart'], async function (e) {

        e.preventDefault();

        var form = document.getElementById('task_form_add');
        var data = new FormData(form);

        data.append('status', '1');
        data.append('session_flash', true);  

        spinner.classList.toggle('hidden');

        let res;
        
        try {
            res = await Pebble.asyncPost('/task/post', data);
            if (res.error === false) {
                location.reload();
            } else {
                Pebble.setFlashMessage(res.message, 'error');
            }
        } catch (e) {
            await Pebble.asyncPostError('/error/log', e.stack);
        } finally {
            spinner.classList.toggle('hidden');
        }
    });

</script>
