<?php

use Pebble\URL;
use Diversen\Lang;

// This is included in the header - but this template does not have a header
require_once 'Template/utils.php';

if (isset($error)) {
    echo "<p class='error'>$error</p>";
    return;
}

foreach ($tasks as $task) :

    $task_title = $title_attr = "$task[title]";
    $task_box_class = '';
    if ($task['status'] == '0') {
        $task_title = "<s>$task_title</s>";
        $task_box_class = ' task-done ';
    }

    $begin_date = date("d/m/Y", strtotime($task['begin_date']));
    $today = date('Y-m-d 00:00:00');
    $is_today = false;
    if ($today == $task['begin_date']) {
        $is_today = true;
    }

    $return_to_after_edit = URL::returnTo("/task/edit/$task[id]", "/project/view/$task[project_id]");
    $return_to_after_time = URL::returnTo("/time/add/$task[id]", "/project/view/$task[project_id]");

?>

    <tr>
        <td class="td-overflow <?= $task_box_class ?> ">
            <span class="priority <?= get_task_priority_class($task) ?>"></span>
            <a title="<?= $title_attr ?>" href="/task/view/<?= $task['id'] ?>"><?= $task_title ?>
        </td>
        <td><?= $begin_date ?></td>
        <td class='xs-hide'><?= $task['time_used'] ?></td>
        <td>
            <div class="action-links">
                <a title="<?= Lang::translate('Edit task') ?>" href="<?= $return_to_after_edit ?>"><?=get_icon('edit')?></a>
                <a title="<?= Lang::translate('Add time to task') ?>" href="<?= $return_to_after_time?>"><?=get_icon('clock')?></a>
                <?php

                if (!$is_today) : ?>
                    <a 
                        title="<?= Lang::translate('Move to today') ?>" 
                        class="move_to_today" 
                        href='#' data-id="<?= $task['id'] ?>"><?=get_icon('today')?>
                    </a>
                <?php endif; ?>
        </td>
    </tr>
<?php endforeach;

if (isset($next)) { ?>
<tr>
    <td>
        <a class="more" href="<?=$next?>"><?=Lang::translate('Show more')?></a>
    </td>
    <td></td>
    <td class="xs-hide"></td>
    <td></td>
</tr>
<?php

}
