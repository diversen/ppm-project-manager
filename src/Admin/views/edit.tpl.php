<?php

declare(strict_types=1);

use App\Admin\HTMLUtils;
use App\AppMain;
use Diversen\Lang;

$disabled = $table['disabled'] ?? [];

?>
<h3>
    <?= HTMLUtils::getBreadcrumb('Edit', $table['table_human']) ?>
</h3>

<form id="form" method="post" action="/admin/table/<?= $table['table'] ?>/edit/<?= $row[$table['primary_key']] ?>">
    <?php

    foreach ($table['columns'] as $key => $column) : ?>

        <?php

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

        $reference_link = HTMLUtils::getReferenceLinkHTML($column, $table['references'], $row[$column], true);
        if ($reference_link) {
            $label .= " ($reference_link) ";
        }

        echo HTMLUtils::getHTMLElement($column_type, $value, $attr, $label);

    endforeach;
    ?>
    <button id="submit" type="submit" name="submit" value="submit"><?= Lang::translate('Update') ?></button>
    <div class="loadingspinner hidden"></div>
</form>
<script type="module" nonce="<?=AppMain::getNonce()?>">
    import {
        Pebble
    } from '/js/pebble.js?v=<?=AppMain::VERSION?>';

    var elem = document.getElementById('submit');
    elem.addEventListener('click', async function(e) {
        e.preventDefault();

        const spinner = document.querySelector('.loadingspinner');
        spinner.classList.toggle('hidden');

        const form = document.getElementById('form');
        const action = form.getAttribute("action")
        const data = new FormData(form);
        console.log(action);

        try {
            const res = await Pebble.asyncPost(action, data);
            console.log(res);
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

</script>