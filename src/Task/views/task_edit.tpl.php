<?php

use Diversen\Lang;
use App\AppMain;

require 'templates/header.tpl.php';
require 'templates/flash.tpl.php';

$begin_date = date('Y-m-d', strtotime($task['begin_date']));
$end_date = date('Y-m-d', strtotime($task['end_date']));

$is_selected = function ($priority, $value) {
    if ($priority == $value) {
        return 'selected';
    }
    return '';
};



?>

<h3 class="sub-menu">
    <a href="/project"><?= Lang::translate('Projects') ?></a><?= SUB_MENU_SEP ?>
    <a href="/project/view/<?= $project['id'] ?>"><?= $project['title'] ?></a><?= SUB_MENU_SEP ?>
    <?= Lang::translate('Edit task') ?>
</h3>


<form id="task_edit" name="task_edit" method="post">
    <label for="title"><?= Lang::translate('Title') ?> *</label>
    <input class="input-large" id="title" type="text" name="title" placeholder="<?= Lang::translate('Enter title') ?>" value="<?= $task['title'] ?>">

    <label for="note"><?= Lang::translate('Add note') ?></label>
    <textarea name="note" placeholder="<?= Lang::translate('Add an optional task note') ?>"><?= $task['note'] ?></textarea>

    <label for="project_id"><?= Lang::translate('Project') ?></label>
    <select name="project_id">
        <?php
        foreach ($all_projects as $_project) : ?>
            <option value="<?= $_project['id'] ?>" <?= $is_selected($_project['id'], $project['id']) ?>><?= $_project['title'] ?></option>
        <?php
        endforeach;

        ?>
    </select>
    <label for="priority"><?= Lang::translate('Priority') ?></label>
    <select name="priority">
        <option value="4" <?= $is_selected('4', $task['priority']) ?>><?= Lang::translate('Urgent') ?></option>
        <option value="3" <?= $is_selected('3', $task['priority']) ?>><?= Lang::translate('High') ?></option>
        <option value="2" <?= $is_selected('2', $task['priority']) ?>><?= Lang::translate('Normal') ?></option>
        <option value="1" <?= $is_selected('1', $task['priority']) ?>><?= Lang::translate('Minor') ?></option>
        <option value="0" <?= $is_selected('0', $task['priority']) ?>><?= Lang::translate('Low') ?></option>
    </select>


    <label for="begin_data"><?= Lang::translate('Task begin date') ?> *</label>
    <input id="begin_date" type="date" name="begin_date" placeholder="<?= Lang::translate('Pick begin date') ?>" value="<?= $begin_date ?>">

    <label for="end_date"><?= Lang::translate('Task end date') ?></label>
    <input id="end_date" type="date" name="end_date" placeholder="<?= Lang::translate('Pick end date') ?>" value="<?= $end_date ?>">

    <input id="id" type="hidden" name="id" value="<?= $task['id'] ?>">
    <input id="status" type="hidden" name="status" value="<?= $task['status'] ?>">

    <button id="task_update" type="submit"><?= Lang::translate('Submit') ?></button>

    <?php

    if ($task['status'] == '1') { ?>
        <button id="task_complete" type="submit"><?= Lang::translate('Complete') ?></button>
    <?php

    }

    if ($task['status'] == '0') { ?>
        <button id="task_open" type="submit"><?= Lang::translate('Open') ?></button>
    <?php

    }
    ?>
    <button id="task_delete" type="submit"><?= Lang::translate('Delete') ?></button>
    <div class="loadingspinner hidden"></div>

</form>

<script type="module" nonce="<?=AppMain::getNonce()?>">
    import {
        Pebble
    } from '/js/pebble.js';

    const return_to = Pebble.getQueryVariable('return_to');
    const title = document.getElementById('title');
    title.focus();

    const spinner = document.querySelector('.loadingspinner');

    async function deleteTask(status) {

        const form = document.getElementById('task_edit');
        const data = new FormData(form);
        const task_id = document.getElementById('id').value;
        const return_to = Pebble.getQueryVariable('return_to');

        try {

            spinner.classList.toggle('hidden');
            const res = await Pebble.asyncPost('/task/delete/' + task_id, data);

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
            Pebble.asyncPostError('/error/log', e.stack)
        } finally {
            spinner.classList.toggle('hidden');
        }
    }


    async function updateTask(status) {

        const form = document.getElementById('task_edit');
        const data = new FormData(form);

        if (status == 'complete') {
            data.append('status', '0')
        }

        if (status == 'open') {
            data.append('status', '1')
        }

        const task_id = document.getElementById('id').value;

        try {
            spinner.classList.toggle('hidden');
            const res = await Pebble.asyncPost('/task/put/' + task_id, data);

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
            Pebble.asyncPostError('/error/log', e.stack)
        } finally {
            spinner.classList.toggle('hidden');
        }
    }

    const task_update = document.getElementById('task_update');
    task_update.addEventListener('click', async function(e) {
        e.preventDefault();
        updateTask();
    })

    const task_complete = document.getElementById('task_complete');
    if (task_complete) {
        task_complete.addEventListener('click', async function(e) {
            e.preventDefault();
            const complete_confirm = confirm('<?= Lang::translate('Complete this task?') ?>')
            if (complete_confirm) {
                updateTask('complete');
            }
        })
    }

    const task_delete = document.getElementById('task_delete');
    if (task_delete) {
        task_delete.addEventListener('click', async function(e) {
            e.preventDefault();
            const complete_confirm = confirm('<?= Lang::translate('Delete this task.Registered time entries will be removed?') ?>')
            if (complete_confirm) {
                deleteTask();
            }
        })
    }

    const task_open = document.getElementById('task_open');
    if (task_open) {
        task_open.addEventListener('click', async function(e) {
            e.preventDefault();
            const complete_confirm = confirm('<?= Lang::translate('Open this task?') ?>')
            if (complete_confirm) {
                updateTask('open');
            }
        })
    }
</script>

<?php

require 'templates/footer.tpl.php';
