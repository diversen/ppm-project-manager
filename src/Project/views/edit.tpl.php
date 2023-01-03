<?php

use Diversen\Lang;
use App\AppMain;

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
    <textarea id="note" name="note" placeholder="<?= Lang::translate('Add an optional project note') ?>"><?= $project['note'] ?></textarea>
    <input id="id" type="hidden" name="id" value="<?= $project['id'] ?>">
    <input id="status" type="checkbox" name="status" value="1" <?= $checked ?>>
    <label for="status"><?= Lang::translate('Project is active') ?></label>
    <br>
    <button id="project_submit" type="submit"><?= Lang::translate('Update') ?></button>
    <button id="project_delete" type="submit"><?= Lang::translate('Delete') ?></button>
    <div class="loadingspinner hidden"></div>
</form>


<script type="module" nonce="<?=AppMain::getNonce()?>">
    import { Pebble } from '/js/pebble.js?v=<?=AppMain::VERSION?>';

    const title = document.getElementById('title');
    title.focus();
    
    const spinner = document.querySelector('.loadingspinner');

    async function updateProject() {
        spinner.classList.toggle('hidden');

        const form = document.getElementById('project_edit');
        const data = new FormData(form);
        const project_id = document.getElementById('id').value;

        try {

            const res = await Pebble.asyncPost('/project/put/' + project_id, data);
            if (res.error === false) {
                Pebble.redirect(res.redirect);
            } else {
                Pebble.setFlashMessage(res.message, 'error');
            }
        } catch (e) {
            Pebble.asyncPostError('/error/log', e.stack)
        }

        spinner.classList.toggle('hidden');
    }

    async function deleteProject() {
        spinner.classList.toggle('hidden');

        const form = document.getElementById('project_edit');
        const data = new FormData(form);
        const project_id = document.getElementById('id').value;

        try {

            const res = await Pebble.asyncPost('/project/delete/' + project_id, data);
            if (res.error === false) {
                Pebble.redirect(res.redirect);
            } else {
                Pebble.setFlashMessage(res.message, 'error');
            }
        } catch (e) {
            Pebble.asyncPostError('/error/log', e.stack)
        } finally {
            spinner.classList.toggle('hidden');
        }
    }

    const project_submit = document.getElementById('project_submit');
    project_submit.addEventListener('click', async function(e) {
        e.preventDefault();
        updateProject()
    })

    const project_delete = document.getElementById('project_delete');
    project_delete.addEventListener('click', async function(e) {

        e.preventDefault();
        const confirm_res = confirm('<?= Lang::translate('Are you sure want to delete this project? All tasks and all time entries will be deleted!') ?>');
        if (!confirm_res) {
            return;
        }
        deleteProject()
    })
</script>