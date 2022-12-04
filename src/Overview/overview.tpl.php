<?php

declare(strict_types=1);

use Pebble\URL;
use Diversen\Lang;
use App\AppMain;
use App\Utils\DateUtils;

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

    $project_title = $task['project_title'] . "\n";
    $project_title.= Lang::translate('Total time used on project:') . " $task[project_time_total]"; 
    
    $add_new_task_title = Lang::translate('Add new task to') . " '$task[project_title]'";
    $add_time_title = Lang::translate('Add time to') . " '$task[title]'";
    $edit_task_title = Lang::translate('Edit task') . " '$task[title]'";
    $move_to_today_title = Lang::translate('Move to today');

    ?>
    <tr>
        <td class="td-overflow <?= $task_box_class ?>" title="<?= $title_attr ?>">
            <span class="priority <?= get_task_priority_class($task) ?>"></span>
            <a href="/task/view/<?= $task['id'] ?>"><?= $task_title ?></a>
        </td>

        <td class='td-overflow'>
            <a title="<?= $project_title ?>" href='/project/view/<?= $task['project_id'] ?>'><?= $task['project_title'] ?></a>
        </td>

        <td class='xs-hide'><?= $task['task_time_total'] ?></td>

        <td>
            <div class="action-links">
                <a title="<?= $edit_task_title ?>" href="<?= URL::returnTo("/task/edit/$task[id]") ?>">
                    <?= get_icon('edit') ?>
                </a>
                <a title="<?= $add_new_task_title ?>" href='<?= URL::returnTo("/task/add/$task[project_id]") ?>'>
                    <?= get_icon('add') ?>
                </a>
                <a title="<?= $add_time_title ?>" href='<?= URL::returnTo("/time/add/$task[id]") ?>'>
                    <?= get_icon('clock') ?>
                </a>

                <?php

                if (!$today) : ?>
                    <a title="<?= $move_to_today_title ?>" class="move_to_today xs-hide" href='#' data-id="<?= $task['id'] ?>">
                        <?= get_icon('today') ?>
                    </a>
                <?php endif; ?>
            </div>
        </td>
    </tr>
    <?php
}

function get_day_name(string $day_num)
{
    $days = [
        '1' => Lang::translate('monday'),
        '2' => Lang::translate('tuesday'),
        '3' => Lang::translate('wednesday'),
        '4' => Lang::translate('thursday'),
        '5' => Lang::translate('friday'),
        '6' => Lang::translate('saturday'),
        '7' => Lang::translate('sunday'),
    ];

    return $days[$day_num];
}

function get_date($ts)
{
    return date('M d, Y', $ts);
}


/**
 * Render a full week
 */
function render_week($week_data, $week_state, $week_user_day_times)
{
    $date_utils = new DateUtils();


    $current_day_state = $week_state['current_day_state'];
    foreach ($week_data as $ts => $day_data) :

        if (empty($day_data)) {
            continue;
        }

    $date_time_user = $date_utils->getUserDateTimeFromUnixTs($ts);
    $day_number = $date_time_user->format("N");
    $is_today = is_today($ts);

    if ($current_day_state == '1' && !$is_today && $week_state['current'] == '0') {
        continue;
    }

    $day_class = '';
    if ($is_today) {
        $day_class = ' class="today" ';
    }

    $date = $date_time_user->format('M d, Y'); ?>

        <p>
            <strong title="" <?= $day_class ?>><?= ucfirst(get_day_name($day_number)) ?> </strong> (<?= $date ?>)
            <?= Lang::translate('Your activity: <span class="notranslate">{activity}</span> ', array('activity' => $week_user_day_times[$ts])) ?>
        </p>
        <table>
            <thead>
                <tr>

                    <td class="width-35"><?= Lang::translate('Task') ?></td>
                    <td><?= Lang::translate('Project') ?></td>
                    <td class='xs-hide' ><?= Lang::translate('Time') ?></td>
                    <td></td>
                </tr>
            </thead>

            <tbody><?php

    foreach ($day_data as $task) :
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
function render_navigation($week_state, $week_user_total, $has_projects)
{
    ?>
    <h3><?= Lang::translate('Week') ?> <?= $week_state['week_number_delta'] ?></h3>
    <div class="action-links">
        <a href="/overview?week_delta=<?= $week_state['prev'] ?>">
            <?= Lang::translate('Show week') ?> <?= $week_state['week_number_delta_prev'] ?>
        </a>
        <a href="/overview?week_delta=<?= $week_state['next'] ?>"><?= Lang::translate('Show week') ?> 
            <?= $week_state['week_number_delta_next'] ?>
        </a>
        <?php if ($week_state['current'] != '0') : ?>
            <a href="/overview"><?= Lang::translate('Current week') ?></a>
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
        <a href="/overview" class="move_exceeded_today">
            <?= Lang::translate('Move exceeded to today') ?>
        </a>
        <?php if ($week_state['current'] == '0') : ?>
            <a href="/overview" class="toggle_current_day" data-current-day-state="<?= $week_state['current_day_state'] ?>">
                <?= $current_day_state_text ?>
            </a>
        <?php endif; ?>
        <?php if ($has_projects) : ?>
            <a href="<?= URL::returnTo("/task/add/project-unknown") ?>" class="add_new_task">
                <?= Lang::translate('Add new task') ?>
            </a>
        <?php endif; ?>
    </div>
    <p>
        <?= Lang::translate('Activity this week: <span class="notranslate">{week_user_total}</span>', array('week_user_total' => $week_user_total)) ?>
    </p>

<?php
};

render_navigation($week_state, $week_user_total, $has_projects);
render_week($week_data, $week_state, $week_user_day_times);

?>

<script type="module" nonce="<?= AppMain::$nonce ?>">
    import {
        Pebble
    } from '/js/pebble.js?v=<?= AppMain::VERSION ?>';

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
            const res = await Pebble.asyncPost('/overview/settings/put', formData);
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