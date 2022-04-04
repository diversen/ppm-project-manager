<?php declare(strict_types=1);

use Pebble\URL;
use Diversen\Lang;

require 'templates/header.tpl.php';

/**
 * Render a single task
 */
function render_task($task, $today)
{
    $task_title = $title_attr = "$task[title]";
    $task_box_class = '';
    if ($task['status'] == '0') {
        $task_title = "<s>$task_title</s>";
        $task_box_class = ' task-done ';
    }

    $project_title = $task['project_title'] . "\n" .
        Lang::translate('Total time used on project: ') . $task['project_time_total']; ?>
    <tr>
        <td class="td-overflow <?=$task_box_class?>" title="<?=$title_attr?>">
            <span class="priority <?=get_task_priority_class($task)?>"></span>
            <a href="/task/view/<?=$task['id']?>"><?=$task_title?></a>
        </td>

        <td class='td-overflow'>
            <a title="<?=$project_title?>" href='/project/view/<?=$task['project_id']?>'><?=$task['project_title']?></a>
        </td>

        <td class='xs-hide'><?=$task['task_time_total']?></td>

        <td>
        <div class="action-links">
            <a title="<?=Lang::translate('Edit task')?>" href="<?=URL::returnTo("/task/edit/$task[id]")?>"><?=Lang::translate('Edit')?></a>
            <a title="<?=Lang::translate('Add new task to project')?>" class="xs-hide" href='<?=URL::returnTo("/task/add/$task[project_id]")?>'><?=Lang::translate('New')?></a>
            <a title="<?=Lang::translate('Add time to task')?>" href='<?=URL::returnTo("/time/add/$task[id]")?>'><?=Lang::translate('Time')?></a>

            <?php

            if (!$today): ?>
            <a title="<?=Lang::translate('Move to today')?>" class="move_to_today xs-hide" href='#' data-id="<?=$task['id']?>"><?=Lang::translate('Today')?></a>
            <?php endif; ?>
        </div>
        </td>
    </tr>
<?php
}

/**
 * Render a full week
 */
function render_week($week_data, $week_state, $week_user_day_times)
{
    $current_day_state = $week_state['current_day_state'];
    foreach ($week_data as $ts => $day_data):

        if (empty($day_data)) {
            continue;
        }

    $day_name = date('D', $ts);
    $is_today = is_today($ts);

    if ($current_day_state == '1' && !$is_today && $week_state['current'] == '0') {
        continue;
    }

    $day_class = '';
    if ($is_today) {
        $day_class = ' class="today" ';
    } ?>

	        <p><strong <?=$day_class?> ><?=$day_name?>. </strong>
            <?=Lang::translate('Your activity: <span class="notranslate">{activity}</span> ', array('activity' => $week_user_day_times[$ts]))?></p>
	        <table>
	            <thead>
	                <tr>

	                    <td class="width-35"><?=Lang::translate('Task')?></td>
	                    <td><?=Lang::translate('Project')?></td>
	                    <td class='xs-hide' class="width-50"><?=Lang::translate('Time')?></td>
	                    <td></td>
	                </tr>
	            </thead>

	            <tbody>
	                <?php

        foreach ($day_data as $task):
            render_task($task, $is_today);
    endforeach; ?>

	            </tbody>
	        </table>
		<?php

    endforeach;
};


/**
 * Render navigation
 */
function render_week_nav($week_state, $week_user_total)
{
    ?>
<h3><?=Lang::translate('Week')?> <?=$week_state['week_number_delta']?></h3>
<div class="action-links">
    <a href="/overview?week_delta=<?=$week_state['prev']?>"><?=Lang::translate('Show week')?> <?=$week_state['week_number_delta_prev']?></a>
    <a href="/overview?week_delta=<?=$week_state['next']?>"><?=Lang::translate('Show week')?> <?=$week_state['week_number_delta_next']?></a>
    <?php if ($week_state['current'] != '0'): ?>
    <a href="/overview"><?=Lang::translate('Current week')?></a>
    <?php endif; ?>
</div>

<?php

if ($week_state['current_day_state'] == '1') {
    $current_day_state_text = Lang::translate('Show full week');
} else {
    $current_day_state_text = Lang::translate('Show today');
} ?>
<div class="clear"></div>
<div class="action-links">
    <a href="/overview" class="move_exceeded_today" ><?=Lang::translate('Move exceeded to today')?></a>
    <?php if ($week_state['current'] == '0'): ?>
    <a href="/overview" class="toggle_current_day" data-current-day-state="<?=$week_state['current_day_state']?>"><?=$current_day_state_text?></a>
    <?php endif; ?>  
</div>
<p><?=Lang::translate('Activity this week: <span class="notranslate">{week_user_total}</span>', array('week_user_total' => $week_user_total))?></p>

<?php
};

render_week_nav($week_state, $week_user_total);
render_week($week_data, $week_state, $week_user_day_times);

?>
<script type="module">
    import {Pebble} from '/js/pebble.js';

    document.addEventListener('click', async function(event) {

        if (!event.target.matches('.move_to_today')) return;
        event.preventDefault();

        const todayElem = document.getElementById(event.target);
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

    document.addEventListener('click', async function(event) {

        if (!event.target.matches('.move_exceeded_today')) return;

        event.preventDefault();

        const todayElem = document.getElementById(event.target);
        const task_id = event.target.dataset.id
        const formData = new FormData();

        try {
            const res = await Pebble.asyncPost('/task/put/exceeded/today', formData);
            if (res.error === false) {
                location.reload();
            } else {
                Pebble.setFlashMessage(res.error, 'error');
            }
        } catch (e) {
            Pebble.asyncPostError('/error/log', e.stack)
        }
    });

    document.addEventListener('click', async function(event) {

        if (!event.target.matches('.toggle_current_day')) return;

        event.preventDefault();

        const state = event.target.dataset.currentDayState
        const formData = new FormData();
        if (state == '0') {
            formData.append('overview_current_day_state', '1')
        } else {
            formData.append('overview_current_day_state', '0')
        }

        try {
            const res = await Pebble.asyncPost('/settings/put/', formData);
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
