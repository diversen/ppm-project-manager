<?php

use Diversen\Lang;
use App\AppMain;
use App\Task\TaskModel;

$begin_date = date('Y-m-d', strtotime($task['begin_date']));
$end_date = date('Y-m-d', strtotime($task['end_date']));

$is_selected = function ($value, $current_state) {
    if ($value == $current_state) {
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
        <option value="<?= TaskModel::PRIORITY_URGENT ?>" <?= $is_selected(TaskModel::PRIORITY_URGENT, $task['priority']) ?>><?= Lang::translate('Urgent') ?></option>
        <option value="<?= TaskModel::PRIORITY_HIGH ?>" <?= $is_selected(TaskModel::PRIORITY_HIGH, $task['priority']) ?>><?= Lang::translate('High') ?></option>
        <option value="<?= TaskModel::PRIORITY_NORMAL ?>" <?= $is_selected(TaskModel::PRIORITY_NORMAL, $task['priority']) ?>><?= Lang::translate('Normal') ?></option>
        <option value="<?= TaskModel::PRIORITY_MINOR ?>" <?= $is_selected(TaskModel::PRIORITY_MINOR, $task['priority']) ?>><?= Lang::translate('Minor') ?></option>
        <option value="<?= TaskModel::PRIORITY_LOW ?>" <?= $is_selected(TaskModel::PRIORITY_LOW, $task['priority']) ?>><?= Lang::translate('Low') ?></option>
    </select>

    <label for="auto_move"><?= Lang::translate('Repeatable task. Will auto-move the task to a new date when the end date of the task is exceeded.') ?></label>
    <select name="auto_move">
        <option value="<?= TaskModel::AUTO_MOVE_NONE ?>" <?= $is_selected(TaskModel::AUTO_MOVE_NONE, $task['auto_move']) ?>><?= Lang::translate('Deactivated') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_TODAY ?>" <?= $is_selected(TaskModel::AUTO_MOVE_TODAY, $task['auto_move']) ?>><?= Lang::translate('Next day') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_ONE_WEEK ?>" <?= $is_selected(TaskModel::AUTO_MOVE_ONE_WEEK, $task['auto_move']) ?>><?= Lang::translate('One week') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_FOUR_WEEKS ?>" <?= $is_selected(TaskModel::AUTO_MOVE_FOUR_WEEKS, $task['auto_move']) ?>><?= Lang::translate('Four weeks') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_FIRST_DAY_OF_NEXT_MONTH ?>" <?= $is_selected(TaskModel::AUTO_MOVE_FIRST_DAY_OF_NEXT_MONTH, $task['auto_move']) ?>><?= Lang::translate('One month. First day') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_LAST_DAY_OF_THIS_MONTH ?>" <?= $is_selected(TaskModel::AUTO_MOVE_LAST_DAY_OF_THIS_MONTH, $task['auto_move']) ?>><?= Lang::translate('One month. Last day') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_FIRST_SAME_DAY_NEXT_MONTH ?>" <?= $is_selected(TaskModel::AUTO_MOVE_FIRST_SAME_DAY_NEXT_MONTH, $task['auto_move']) ?>><?= Lang::translate("One month. First day same day name.") ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_LAST_SAME_DAY_NEXT_MONTH ?>" <?= $is_selected(TaskModel::AUTO_MOVE_LAST_SAME_DAY_NEXT_MONTH, $task['auto_move']) ?>><?= Lang::translate('One month. Last day same day name') ?></option>
        <option value="<?= TaskModel::AUTO_MOVE_CLOSE_TODAY ?>" <?= $is_selected(TaskModel::AUTO_MOVE_CLOSE_TODAY, $task['auto_move']) ?>><?= Lang::translate('Close task after end date') ?></option>
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

<script type="module" nonce="<?= AppMain::getNonce() ?>">
    import {Pebble} from '/js/pebble.js?v=<?= AppMain::VERSION ?>';
    import {addMultipleEventListener} from '/js/event.js?v=<?= AppMain::VERSION ?>'

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
                Pebble.redirect(res.redirect);
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
                    Pebble.redirect(return_to);
                } else {
                    Pebble.redirect(res.redirect);
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
    addMultipleEventListener(task_update, ['click', 'touchstart'], async function(e) {
        e.preventDefault();
        updateTask();
    })

    const task_complete = document.getElementById('task_complete');
    if (task_complete) {
        addMultipleEventListener(task_complete, ['click', 'touchstart'], async function(e) {
            e.preventDefault();
            const complete_confirm = confirm('<?= Lang::translate('Complete this task?') ?>')
            if (complete_confirm) {
                updateTask('complete');
            }
        })
    }

    const task_delete = document.getElementById('task_delete');
    if (task_delete) {
        addMultipleEventListener(task_delete, ['click', 'touchstart'], async function(e) {
            e.preventDefault();
            const complete_confirm = confirm('<?= Lang::translate('Delete this task. Registered time entries will be removed?') ?>')
            if (complete_confirm) {
                deleteTask();
            }
        })
    }

    const task_open = document.getElementById('task_open');
    if (task_open) {
        addMultipleEventListener(task_open, ['click', 'touchstart'], async function(e) {
            e.preventDefault();
            const complete_confirm = confirm('<?= Lang::translate('Open this task?') ?>')
            if (complete_confirm) {
                updateTask('open');
            }
        })
    }
</script>