<?php

use Diversen\Lang;
use Pebble\URL;
use App\AppMain;

$parsedown = new Parsedown();
$parsedown->setSafeMode(true);

$note = \Pebble\Special::decodeStr($task['note']);
$note_markdown = $parsedown->text($note);

require 'templates/header.tpl.php';

$begin_date = date('Y-m-d', strtotime($task['begin_date']));

?>
<h3 class="sub-menu">
    <a href="/project"><?=Lang::translate('Projects')?></a><?=SUB_MENU_SEP?>
    <a href="/project/view/<?=$project['id']?>"><?=$project['title']?></a><?=SUB_MENU_SEP?>
    <?=$task['title']?>
</h3>


<table>
	<thead>
		<tr>
			<td class="width-35"><?=Lang::translate('Task')?></td>
			<td><?=Lang::translate('Project')?></td>
			<td class='xs-hide'><?=Lang::translate('Time')?></td>
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
            <span><?=$task_title?></span>
        </td>

        <td class='td-overflow'>
            <a title="<?=$project['title']?>" href='/project/view/<?=$task['project_id']?>'><?=$project['title']?></a>
        </td>

        <td class='xs-hide'><?=$task['task_time_total']?></td>

        <td>
        <div class="action-links">
            <a title="<?=Lang::translate('Edit task')?>" href="<?=URL::returnTo("/task/edit/$task[id]")?>"><?=get_icon('edit')?></a>
            <a title="<?=Lang::translate('Add new task to project')?>" class="xs-hide" href='<?=URL::returnTo("/task/add/$task[project_id]")?>'><?=get_icon('add')?></a>
            <a title="<?=Lang::translate('Add time to task')?>"  href='<?=URL::returnTo("/time/add/$task[id]")?>'><?=get_icon('clock')?></a>

            <?php

            if (!$today): ?>
            <a title="<?=Lang::translate('Move to today')?>"  class="xs-hide" href='#' data-id="<?=$task['id']?>" class="move_to_today"><?=get_icon('today')?></a>
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
<script type="module" nonce="<?=AppMain::getNonce()?>">

import {Pebble} from '/js/pebble.js';

document.addEventListener('click', async function(event) {

    if (!event.target.matches('.move_to_today')) return;

    event.preventDefault();

    const task_id = event.target.dataset.id
    
    const formData = new FormData();
    formData.append('now', 'true')
    formData.append('id', task_id);

    try {
        const res = await Pebble.asyncPost('/task/put/' + task_id, formData);
        if (res.error === false) {
            location.reload();
        } else {
            Pebble.setFlashMessage(res.error, 'error');
        }
    } catch (e) {
        Pebble.asyncPostError('/error/log', e.stack)
    }
});
</script>
<?php

require 'templates/footer.tpl.php';
