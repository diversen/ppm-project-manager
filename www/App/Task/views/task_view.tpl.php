<?php

use \Diversen\Lang;
use \Pebble\URL;

$parsedown = new Parsedown();
$parsedown->setSafeMode(true);

$note = \Pebble\Special::decodeStr($task['note']);
$note_markdown = $parsedown->text($note);

require 'App/templates/header.tpl.php';

$begin_date = date('Y-m-d', strtotime($task['begin_date']));

?>
<h3 class="sub-menu">
    <a href="/project"><?=Lang::translate('Projects')?></a><?=SUB_MENU_SEP?>
    <a href="/project/view/<?=$project['id']?>"><?=$project['title']?></a><?=SUB_MENU_SEP?>
    <?=Lang::translate('View task')?>
</h3>


<table>
	<thead>
		<tr>
			<td style="width:35%"><?=Lang::translate('Task')?></td>
			<td><?=Lang::translate('Project')?></td>
			<td class='xs-hide' style="width:50px"><?=Lang::translate('Time')?></td>
			<td></td>
		</tr>
	</thead>

	<tbody>
	<?php


	$today = is_today(strtotime($task['begin_date']));
    $task_title = $title_attr = "$task[title]";
    $task_box_class = '';
    if ($task['status'] == '0') {
        $task_title = "<s>$task_title</s>";
        $task_box_class = ' task-done ';
    }


    ?>
    <tr>
        <td class="td-overflow <?=$task_box_class?>" title="<?=$title_attr?>">
            <span class="priority <?=get_task_priority_class($task)?>"></span>
            <a href="/task/view/<?=$task['id']?>"><?=$task_title?></a>
        </td>

        <td class='td-overflow'>
            <a title="<?=$project['title']?>" href='/project/view/<?=$task['project_id']?>'><?=$project['title']?></a>
        </td>

        <td class='xs-hide'><?=$task['task_time_total']?></td>

        <td>
        <div class="action-links">
            <a title="<?=Lang::translate('Edit task')?>" href="<?=URL::returnTo("/task/edit/$task[id]")?>"><?=Lang::translate('Edit')?></a>
            <a title="<?=Lang::translate('Add new task to project')?>" class="xs-hide" href='<?=URL::returnTo("/task/add/$task[project_id]")?>'><?=Lang::translate('New')?></a>
            <a title="<?=Lang::translate('Add time to task')?>"  href='<?=URL::returnTo("/time/add/$task[id]")?>'><?=Lang::translate('Time')?></a>

            <?php

            if (!$today): ?>
            <a title="<?=Lang::translate('Move to today')?>" href='#' data-id="<?=$task['id']?>" class="move_to_today"><?=Lang::translate('Today')?></a>
            <?php endif;?>
        </td>
    </tr>
	</tbody>
</table>
<?php

if (!empty($note)):?>
<p><strong><?=Lang::translate('Note')?></strong></p>
<?php
endif;

echo $note_markdown;

?>
<script type="module">

import {Pebble} from '/App/js/pebble.js';
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
        console.log(e)
    }
});
</script>
<?php

require 'App/templates/footer.tpl.php';