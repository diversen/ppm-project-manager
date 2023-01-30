<?php

declare(strict_types=1);

use Diversen\Lang;
use Pebble\URL;
use App\AppMain;
use App\Utils\HTMLUtils;

$parsedown = new Parsedown();
$parsedown->setSafeMode(true);

$note = \Pebble\Special::decodeStr($task['note']);
$note_markdown = $parsedown->text($note);

$begin_date = date('Y-m-d', strtotime($task['begin_date']));

?>
<h3 class="sub-menu">
    <a href="/project"><?=Lang::translate('Projects')?></a><?=HTMLUtils::getMenuSeparator()?>
    <a href="/project/view/<?=$project['id']?>"><?=$project['title']?></a><?=HTMLUtils::getMenuSeparator()?>
    <?=$task['title']?>
</h3>

<p><?= Lang::translate('Total time used on task') ?>: <strong><?= $task['task_time_total'] ?></strong></p>

<table>
	<thead>
		<tr>
			<td class="width-35"><?=Lang::translate('Task')?></td>
			<td><?=Lang::translate('Project')?></td>
			<td></td>
		</tr>
	</thead>

	<tbody>
	<?php

    $today = HTMLUtils::isToday(strtotime($task['begin_date']));
    $task_title = $title_attr = "$task[title]";
    $task_box_class = '';
    if ($task['status'] == '0') {
        $task_title = "<s>$task_title</s>";
        $task_box_class = ' task-done ';
    }

    ?>
    <tr>
        <td class="td-overflow <?=$task_box_class?>" title="<?=$title_attr?>">
            <span class="priority <?=HTMLUtils::getTaskPriorityClass($task)?>"></span>
            <span><?=$task_title?></span>
        </td>

        <td class='td-overflow'>
            <a title="<?=$project['title']?>" href='/project/view/<?=$task['project_id']?>'><?=$project['title']?></a>
        </td>

        <td>
        <div class="action-links">
            <a title="<?=Lang::translate('Edit task')?>" href="<?=URL::returnTo("/task/edit/$task[id]")?>"><?=HTMLUtils::getIcon('edit')?></a>
            <a title="<?=Lang::translate('Add new task to project')?>" class="xs-hide" href='<?=URL::returnTo("/task/add/$task[project_id]")?>'><?=HTMLUtils::getIcon('add')?></a>
            <a title="<?=Lang::translate('Add time to task')?>"  href='<?=URL::returnTo("/time/add/$task[id]")?>'><?=HTMLUtils::getIcon('clock')?></a>

            <?php

            if (!$today): ?>
            <a title="<?=Lang::translate('Move to today')?>"  class="xs-hide" href='#' data-id="<?=$task['id']?>" class="move_to_today"><?=HTMLUtils::getIcon('today')?></a>
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
<script type="module" nonce="<?=AppMain::getNonce();?>">

import {Pebble} from '/js/pebble.js?v=<?=AppMain::VERSION?>';

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
