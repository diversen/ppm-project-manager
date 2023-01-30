<?php

declare(strict_types=1);

use App\Admin\HTMLUtils;
use App\AppMain;
use Diversen\Lang;

$disabled = $table['disabled'] ?? [];
$redirect = $_SERVER['HTTP_REFERER'] ?? '/admin';

?>
<h3 class="sub-menu">
    <?= HTMLUtils::getBreadcrumb('Edit', $table['table_human']) ?>
</h3>
<?php

if ($error): ?>
<div class="error"><?=$error?></div>
<?php

return;

endif;

?>

<form id="form" method="post">
    <?php

    foreach ($table['columns'] as $key => $column):

        $attr = [];
        $attr['class'] = 'form-control';
        $attr['id'] = $column;
        $attr['name'] = $column;

        if (HTMLUtils::isDisabled($column, $disabled)) {
            $attr['disabled'] = 'disabled';
        }

        $value = $row[$column];
        $column_type = $table['columns_type'][$column];
        $label = $table['columns_human'][$key];

        $reference_link = HTMLUtils::getReferenceLinkHTML($column, $table['references'], $value, true);
        if ($reference_link) {
            $label .= " ($reference_link) ";
        }

        echo HTMLUtils::getHTMLElement($column_type, $value, $attr, $label);

    endforeach;
    ?>
    <button id="submit" type="submit" name="submit" value="submit"><?= Lang::translate('Update') ?></button>
    <button id="delete" type="submit" name="delete" value="delete"><?= Lang::translate('Delete') ?></button>
    <div class="loadingspinner hidden"></div>
</form>
<script type="module" nonce="<?=(new AppMain())->getNonce();?>">
    import {
        Pebble
    } from '/js/pebble.js?v=<?=AppMain::VERSION?>';

    const table = Pebble.getPathPart(2);
    const id = Pebble.getPathPart(4);
    const spinner = document.querySelector('.loadingspinner');

    var submitElem = document.getElementById('submit');
    
    submitElem.addEventListener('click', async function(e) {
        e.preventDefault();

        spinner.classList.toggle('hidden');

        const form = document.getElementById('form');
        const putAction = `/admin/table/${table}/put/${id}`;
        const data = new FormData(form);

        try {
            const res = await Pebble.asyncPost(putAction, data);
            console.log(res)
            if (res.error === false) {
                Pebble.setFlashMessage(res.message, 'success');
                
            } else {
                Pebble.setFlashMessage(res.message, 'error');
            }
        } catch (e) {
            Pebble.asyncPostError('/error/log', e.stack)
        } finally {
            spinner.classList.toggle('hidden');
        }
    })

    var deleteElem = document.getElementById('delete');
    deleteElem.addEventListener('click', async function(e) {
        e.preventDefault();

        if (!confirm('<?=Lang::translate('Are you sure you want to delete this row?')?>')) {
            return;
        }

        spinner.classList.toggle('hidden');

        const form = document.getElementById('form');
        const deleteAction = `/admin/table/${table}/delete/${id}`;
        const data = new FormData(form);

        try {

            const res = await Pebble.asyncPost(deleteAction, data);

            if (res.error === false) {
                Pebble.redirect('<?=$redirect?>');
            } else {
                Pebble.setFlashMessage(res.message, 'error');
            }
        } catch (e) {
            Pebble.asyncPostError('/error/log', e.stack)
        } finally {
            spinner.classList.toggle('hidden');
        }
    })

</script>