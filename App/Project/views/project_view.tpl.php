<?php

use \Pebble\URL;
use \Diversen\Lang;

$parsedown = new Parsedown();
$parsedown->setSafeMode(true);

require 'App/templates/header.tpl.php';

$note = \Pebble\Special::decodeStr($project['note']);
$note_markdown = $parsedown->text($note);

?>

<h3 class="sub-menu">
  <a href="/project"><?=Lang::translate('Projects')?></a><?=SUB_MENU_SEP?> 
  <?=$project['title']?>
</h3>

<div class="action-links">
  <a href="/task/add/<?=$project['id']?>"><?=Lang::translate('Add task')?></a>
</div>

<p><?=$note_markdown?></p>

<?php

function render_project_tasks ($tasks) { ?>

<table class="project-table">
  <thead>
    <tr>
      <td style='width:35%'><?=Lang::translate('Task')?></td>
      <td><?=Lang::translate('Date')?></td>
      <td class='xs-hide'><?=Lang::translate('Time')?></td>
      <td></td>
    </tr>
  </thead>
  <tbody>

    <?php

  foreach ($tasks as $task):

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
      
      ?>

    <tr>
      <td class="td-overflow <?=$task_box_class?> ">
        <span class="priority <?=get_task_priority_class($task)?>"></span>
        <a title="<?=$title_attr?>" href="/task/view/<?=$task['id']?>"><?=$task_title?>
      </td>
      <td><?=$begin_date?></td>
      <td class='xs-hide'><?=$task['time_used']?></td>
      <td>
        <div class="action-links">
            <a title="<?=Lang::translate('Edit task')?>" href="<?=URL::returnTo("/task/edit/$task[id]")?>"><?=Lang::translate('Edit')?></a>
            <!--<a title="Add new task to project" class="xs-hide" href='<?=URL::returnTo("/task/add/$task[project_id]")?>'>New</a>-->
            <a title="<?=Lang::translate('Add time to task')?>" href='<?=URL::returnTo("/time/add/$task[id]")?>'><?=Lang::translate('Time')?></a>

            <?php

            if (!$is_today): ?>
            <a title="<?=Lang::translate('Move to today')?>" class="move_to_today xs-hide" href='#' data-id="<?=$task['id']?>"><?=Lang::Translate('Today')?></a>
            <?php endif;?>
        </td>
    </tr>
    <?php endforeach; ?>

  </tbody>
</table>
<?php
};

echo "<p>" . Lang::translate('Total time used on project') . ': ' .  "<strong>$project_time</strong></p>";

if (!empty($tasks)) {
  
  echo "<p><strong>" . Lang::translate('Tasks waiting') . "</strong></p>";
  render_project_tasks($tasks);
}



if (!empty($tasks_completed)) {

  echo "<p><strong>" . Lang::translate('Completed tasks') . '</strong></p>';
  render_project_tasks($tasks_completed);
}

?>
<script>
  document.addEventListener('click', async function (event) {

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