<?php

use Diversen\Lang;

use App\Utils\AppPagination;
use Pebble\Pagination\PaginationUtils;
use App\AppMain;
use App\Utils\HTMLUtils;

?>

<h3 class="sub-menu">
    <a href="/project"><?=Lang::translate('Projects')?></a><?=HTMLUtils::getMenuSeparator()?>
    <a href="/project/view/<?=$project['id']?>"><?=$project['title']?></a><?=HTMLUtils::getMenuSeparator()?>
    <a href="/task/view/<?=$task['id']?>"><?=$task['title']?></a><?=HTMLUtils::getMenuSeparator()?>
    <?=Lang::translate('Time')?>
</h3>

<p><?= Lang::translate('Total time used on task') ?>: <strong><?= $task['task_time_total'] ?></strong></p>

<form id="time_add" name="time_add" method="post">
    <label for="minutes"><?= Lang::translate('Time used. Valid time input (hh:mm), e.g. 1:10 or 0:15') ?> *</label>
    <input id="minutes" type="text" name="minutes" placeholder="<?= Lang::translate('Time used') ?>" value="">

    <label for="note"><?= Lang::translate('Note') ?></label>
    <textarea name="note" placeholder="<?= Lang::translate('Add an optional note') ?>"></textarea>

    <label for="begin_data">Date</label>
    <input id="begin_date" type="date" name="begin_date" placeholder="<?= Lang::translate('Pick date') ?>" value="<?= date('Y-m-d') ?>">

    <input id="task_id" type="hidden" name="task_id" value="<?= $task['id'] ?>">

    <button id="time_add_submit" type="submit" name="submit" value="submit"><?= Lang::translate('Submit') ?></button>
    <button id="time_add_submit_and_stay" type="submit" name="submit" value="submit"><?= Lang::translate('Submit and stay') ?></button>
    <button id="time_add_submit_and_close" type="submit" name="submit" value="submit"><?= Lang::translate('Submit and close task') ?></button>
    <div class="loadingspinner hidden"></div>

</form>

<?php

function output_time_table($time_rows)
{
    $pagination_utils = new PaginationUtils(['begin_date' => 'DESC'], 'time'); ?>

<div id="time-entries">
    <table>
        <thead>
            <tr>
                <td>
                    <?= Lang::translate('Time') ?>
                </td>
                <td>
                    <a href="<?=$pagination_utils->getAlterOrderUrl('begin_date')?>#time-entries">
                        <?= Lang::Translate('Date') ?> <?=$pagination_utils->getCurrentDirectionArrow('begin_date')?>
                    </a>
                </td>
                <td class=>
                    <?= Lang::translate('Note') ?>
                </td>
                <td>

                </td>

            </tr>
        </thead>
        <tbody>

            <?php

            foreach ($time_rows as $key => $val) :
                $begin_date = date("d/m/Y", strtotime($val['begin_date'])); ?>
                <tr>

                    <td class="td-overflow"><?= $val['minutes_hours'] ?></td>
                    <td class="td-overflow"><?= $begin_date ?></td>
                    <td title="<?= $val['note'] ?>" class="td-overflow"><?= $val['note'] ?></td>
                    <td>
                        <div class="action-links">
                            <a class='time_delete' data-id="<?= $val['id'] ?>" href="#"><?=HTMLUtils::getIcon('delete')?></a>
                        </div>
                    <td>
                </tr>
            <?php

            endforeach; ?>
        </tbody>
    </table>
</div>

<?php
}

if (!empty($time_rows)) {
    output_time_table($time_rows);

    $pagination = new AppPagination();
    $pagination->render($paginator);
}

?>
<script type="module" nonce="<?=AppMain::getNonce();?>">
    import { Pebble } from '/js/pebble.js?v=<?=AppMain::VERSION?>';

    const minutes = document.getElementById('minutes');
    minutes.focus();

    const spinner = document.querySelector('.loadingspinner');

    document.addEventListener('click', async function(event) {

        if (event.target.matches('#time_add_submit') || event.target.matches('#time_add_submit_and_stay') || event.target.matches('#time_add_submit_and_close')) {

            event.preventDefault();

            if (event.target.matches('#time_add_submit_and_close')) {
                if (!confirm('<?=Lang::translate('Complete this task?')?>')) {
                    return;
                }
            }

            spinner.classList.toggle('hidden');

            const form = document.getElementById('time_add');
            const data = new FormData(form);
            const return_to = Pebble.getQueryVariable('return_to');

            if (event.target.matches('#time_add_submit_and_close')) {
                data.append('close', 'true');
            }

            try {
                const res = await Pebble.asyncPost('/time/post', data);
                if (res.error === false) {
                    if (event.target.matches('#time_add_submit_and_stay')) {
                        location.reload();
                        return;
                    }
                    if (return_to) {
                        Pebble.redirect(return_to);
                    } else {
                        Pebble.redirect(res.redirect);
                    }
                } else {
                    Pebble.setFlashMessage(res.message, 'error');
                }
            } catch (e) {
                Pebble.asyncPostError('/error/log', e.stack)
            } finally {
                spinner.classList.toggle('hidden');
            }

            
        }

        if (event.target.matches('.time_delete')) {

            event.preventDefault();

            const item = event.target;
            const data = new FormData();
            const id = item.getAttribute('data-id')
            const return_to = Pebble.getQueryVariable('return_to');
            const confirm_res = confirm('<?= Lang::translate('Are you sure you want to delete time entry?') ?>');
            
            if (confirm_res) {

                spinner.classList.toggle('hidden');
                try {
                    const res = await Pebble.asyncPost('/time/delete/' + id, data);

                    if (res.error === false) {
                        location.reload();

                    } else {
                        Pebble.setFlashMessage(res.message, 'error');
                    }

                } catch (e) {
                    Pebble.asyncPostError('/error/log', e.stack)
                } finally {
                    spinner.classList.toggle('hidden');
                }
                
            }
        }
    });
</script>