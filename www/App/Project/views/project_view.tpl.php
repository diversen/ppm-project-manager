<?php

use Diversen\Lang;

$parsedown = new Parsedown();
$parsedown->setSafeMode(true);

require 'App/templates/header.tpl.php';

$note = \Pebble\Special::decodeStr($project['note']);
$note_markdown = $parsedown->text($note);

?>

<h3 class="sub-menu">
    <a href="/project"><?= Lang::translate('Projects') ?></a><?= SUB_MENU_SEP ?>
    <?= $project['title'] ?>
</h3>

<div class="action-links">
    <a href="/task/add/<?= $project['id'] ?>"><?= Lang::translate('Add task') ?></a>
</div>

<p><?= $note_markdown ?></p>
<p><?= Lang::translate('Total time used on project') ?>: <strong><?= $project_time ?></strong></p>


<?php

if (!empty($tasks)) { ?>
    <p><strong><?= Lang::translate('Tasks waiting') ?> (<?= $tasks_count ?>)</strong> </p>

    <table class="project-table">
        <thead>
            <tr>
                <td style='width:35%'><?= Lang::translate('Task') ?></td>
                <td><?= Lang::translate('Date') ?></td>
                <td class='xs-hide'><?= Lang::translate('Time') ?></td>
                <td></td>
            </tr>
        </thead>
        <tbody class="project-tasks" id="tasks-waiting"></tbody>
    </table>
<?php

}

if (!empty($tasks_completed)) { ?>
    <p><strong><?= Lang::translate('Completed tasks') ?> (<?= $tasks_completed_count ?>)</strong></p>
    <table class="project-table">
        <thead>
            <tr>
                <td style='width:35%'><?= Lang::translate('Task') ?></td>
                <td><?= Lang::translate('Date') ?></td>
                <td class='xs-hide'><?= Lang::translate('Time') ?></td>
                <td></td>
            </tr>
        </thead>
        <tbody class="project-tasks" id="tasks-completed"></tbody>
    </table>

<?php

}

?>
<script type="module">

    // let spinner = document.querySelector('.loadingspinner')
    async function loadHtml(url) {
        return fetch(url)
            .then((response) => {
                return response.text();
            })
            .then((html) => {
                return html;
            });
    }

    // spinner.classList.toggle('hidden');

    let tasksWaiting = document.getElementById('tasks-waiting');
    if (tasksWaiting) {
        let html = await loadHtml('/project/tasks/<?= $project['id'] ?>?status=1&from=1')
        tasksWaiting.innerHTML = html
    }
    
    let tasksCompleted = document.getElementById('tasks-completed');
    if(tasksCompleted) {
        let html = await loadHtml('/project/tasks/<?= $project['id'] ?>?status=0&from=1')
        tasksCompleted.innerHTML = html
    }
    
    // spinner.classList.toggle('hidden');

    import {
        Pebble
    } from '/App/js/pebble.js';


    document.addEventListener('click', async function(event) {

        if (!event.target.matches('.move_to_today')) return;
        event.preventDefault();

        let todayElem = document.getElementById(event.target);
        let task_id = event.target.dataset.id

        let res;

        let formData = new FormData();
        formData.append('now', 'true')
        formData.append('id', task_id);

        try {
            res = await Pebble.asyncPost('/task/put/' + task_id, formData);
            if (res.error === false) {
                location.reload();
            } else {
                Pebble.setFlashMessage(res.error, 'error');
            }
        } catch (e) {
            await Pebble.asyncPostError('/error/log', e.stack);
        }
    });
</script>
<?php

require 'App/templates/footer.tpl.php';
