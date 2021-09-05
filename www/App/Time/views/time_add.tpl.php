<?php

use Diversen\Lang;

require 'App/templates/header.tpl.php';
require 'App/templates/flash.tpl.php';

?>

<h3><?=Lang::translate('Add time')?>: <?=$task['title']?></h3>

<p><?=Lang::translate('Project')?>: <a href="/project/view/<?=$project['id']?>"><?=$project['title']?></a></p>

<form id="time_add" name="time_add" method="post">
    <label for="minutes"><?=Lang::translate('Time used. Valid time input (hh:mm), e.g. 1:10 or 0:15')?> *</label>
    <input id="minutes" type="text" name="minutes" placeholder="<?=Lang::translate('Time used')?>" value="">

    <label for="note"><?=Lang::translate('Note')?></label>
    <textarea name="note" placeholder="<?=Lang::translate('Add an optional note')?>"></textarea>

    <label for="begin_data">Date</label>
    <input id="begin_date" type="date" name="begin_date" placeholder="<?=Lang::translate('Pick date')?>" value="<?=date('Y-m-d')?>">
    
    <input id="task_id" type="hidden" name="task_id" value="<?=$task['id']?>">

    <button id="time_add_submit" type="submit" name="submit" value="submit"><?=Lang::translate('Submit')?></button>
    <button id="time_add_submit_and_stay" type="submit" name="submit" value="submit"><?=Lang::translate('Submit and stay')?></button>
	<button id="time_add_submit_and_close" type="submit" name="submit" value="submit"><?=Lang::translate('Submit and close task')?></button>
    <div class="loadingspinner hidden"></div>

</form>

<div>
    <table>
        <thead>
            <tr>
                <td >
                    <?=Lang::translate('Time')?>
                </td>
                <td >
                    <?=Lang::Translate('Date')?>
                </td>
                <td class= >
                    <?=Lang::translate('Note')?>
                </td>
                <td >

                </td>

            </tr>
        </thead>
        <tbody>

    <?php

foreach ($time_rows as $key => $val):
    $created = date("d/m/Y", strtotime($val['created']));
?>
            <tr>
                
                <td class="td-overflow"><?=$val['minutes_hours']?></td>
                <td class="td-overflow"><?=$created?></td>
                <td title="<?=$val['note']?>" class="td-overflow"><?=$val['note']?></td>
                <td>
                    <div class="action-links">
                        <a class='time_delete' data-id="<?=$val['id']?>" href="#"><?=Lang::translate('Delete')?></a>
                    </div>
                <td>
            </tr>
<?php

endforeach;

?>
        </tbody>
    </table>
</div>

<script>

let spinner = document.querySelector('.loadingspinner');

document.addEventListener('click', async function(event) {

	if (event.target.matches('#time_add_submit') || event.target.matches('#time_add_submit_and_stay') || event.target.matches('#time_add_submit_and_close') ) {

		event.preventDefault();

		spinner.classList.toggle('hidden');

		let form = document.getElementById('time_add');
		let data = new FormData(form);
		let return_to = Pebble.getQueryVariable('return_to');

		if (event.target.matches('#time_add_submit_and_close')) {
			data.append('close', 'true');
		}

		let res;
		try {
			res = await Pebble.asyncPost('/time/post', data);
			spinner.classList.toggle('hidden');
			if (res.error === false) {
				if (event.target.matches('#time_add_submit_and_stay')) {
					location.reload();
					return;
				}
				if (return_to) {
					window.location.replace(return_to);
				} else {
					window.location.replace(res.project_redirect);
				}
			} else {
				Pebble.setFlashMessage(res.error, 'error');
			}
		} catch (e) {
			spinner.classList.toggle('hidden');
			Pebble.setFlashMessage(e.message, 'error');
		}
	}

    if (event.target.matches('.time_delete')) {

		event.preventDefault();

		let item = event.target;
		let data = new FormData();
		let id = item.getAttribute('data-id')
		let return_to = Pebble.getQueryVariable('return_to');
		let res;

		spinner.classList.toggle('hidden');

		let confirm_res = confirm('<?=Lang::translate('Are you sure you want to delete time entry?')?>');
		if (confirm_res) {
			try {
				res = await Pebble.asyncPost('/time/delete/' + id, data);
				spinner.classList.toggle('hidden');
				if (res.error === false) {
					location.reload();

				} else {
					Pebble.setFlashMessage(res.error, 'error');
				}
				
			} catch (e) {
				spinner.classList.toggle('hidden');
				Pebble.setFlashMessage(e.message, 'error');
			}
		} else {
			spinner.classList.toggle('hidden');
		}
	}
});

</script>

<?php

require 'App/templates/footer.tpl.php';
