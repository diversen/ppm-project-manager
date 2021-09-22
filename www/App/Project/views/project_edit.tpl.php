<?php

use Diversen\Lang;

include 'App/templates/header.tpl.php';

$checked = '';
if ($project['status'] == 1) {
	$checked = 'checked="checked"';
}

?>

<h3 class="sub-menu">
	<a href="/project"><?= Lang::translate('Projects') ?></a><?= SUB_MENU_SEP ?>
	<a href="/project/view/<?= $project['id'] ?>"><?= $project['title'] ?></a><?= SUB_MENU_SEP ?>
	<?= Lang::translate('Edit project') ?>
</h3>

<h3 class="page-title"><?= $project['title'] ?></h3>

<form id="project_edit" name="project" method="post">
	<label for="title"><?= Lang::translate('Title') ?> *</label>
	<input id="title" type="text" name="title" placeholder="<?= Lang::translate('Enter title') ?>" value="<?= $project['title'] ?>" class="input-large">
	<label for="note">Note</label>
	<textarea name="note" placeholder="<?= Lang::translate('Add an optional project note') ?>"><?= $project['note'] ?></textarea>
	<input id="id" type="hidden" name="id" value="<?= $project['id'] ?>">


	<input type="checkbox" name="status" value="1" <?= $checked ?>>
	<label for="status"><?= Lang::translate('Project is active') ?></label>
	<br />
	<button id="project_submit" type="submit"><?= Lang::translate('Update') ?></button>
	<button id="project_delete" type="submit"><?= Lang::translate('Delete') ?></button>
	<div class="loadingspinner hidden"></div>
</form>


<script type="module">
	import {Pebble} from '/App/js/pebble.js';
	var spinner = document.querySelector('.loadingspinner');

	async function updateProject() {
		spinner.classList.toggle('hidden');

		var form = document.getElementById('project_edit');
		var data = new FormData(form);

		let project_id = document.getElementById('id').value;

		// Manipulate status so we can send it to the same endpoint
		if (!data.has('status')) {
			data.set('status', '0');
		}

		try {
			let res = await Pebble.asyncPost('/project/put/' + project_id, data);
			// spinner.classList.toggle('hidden');

			if (res.error === false) {
				window.location.replace(res.project_redirect);
			} else {
				spinner.classList.toggle('hidden');
				Pebble.setFlashMessage(res.error, 'error');
			}
		} catch (e) {
			console.log(e)
		}
	}

	async function deleteProject() {
		spinner.classList.toggle('hidden');

		var form = document.getElementById('project_edit');
		var data = new FormData(form);

		// Manipulate status so we can send it to the same endpoint
		if (!data.has('status')) {
			data.set('status', '0');
		}

		let project_id = document.getElementById('id').value;

		try {
			let res = await Pebble.asyncPost('/project/delete/' + project_id, data);
			spinner.classList.toggle('hidden');

			if (res.error === false) {
				window.location.replace(res.project_redirect);
			} else {
				spinner.classList.toggle('hidden');
				Pebble.setFlashMessage(res.error, 'error');
			}
		} catch (e) {
			console.log(e)
		}
	}

	var project_submit = document.getElementById('project_submit');
	project_submit.addEventListener('click', async function(e) {
		e.preventDefault();
		updateProject()
	})

	var project_delete = document.getElementById('project_delete');
	project_delete.addEventListener('click', async function(e) {

		e.preventDefault();
		var confirm_res = confirm('<?= Lang::translate('Are you sure want to delete this project? All tasks and all time entries will be deleted!') ?>');
		if (!confirm_res) {
			return;
		}
		deleteProject()
	})
</script>
<?php

require 'App/templates/footer.tpl.php';
