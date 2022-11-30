<?php

declare(strict_types=1);

use App\Utils\AppPagination;
use App\Admin\HTMLUtils;
use Pebble\Pagination\PaginationUtils;

$pagination_utils = new PaginationUtils($order_by, $table['table']);

?>
<h3><a href="/admin">Admin</a> <?=ADMIN_SUB_MENU_SEP?> <?= $table['table_human'] ?></h3>
<table class="admin">
    <thead>
        <tr>
            <?php foreach ($table['columns'] as $key => $column) : ?>
                <th>
                    <a href="<?= $pagination_utils->getAlterOrderUrl($column) ?>">
                    <?= $table['columns_human'][$key] ?> <?= $pagination_utils->getCurrentDirectionArrow($column) ?>
                </th>
            <?php endforeach; ?>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row) : ?>
            <tr>
                <?php foreach ($table['columns'] as $col) : 
                    
                    $reference_link = HTMLUtils::getReferenceLinkHTMLOrValue($col, $table['references'], $row[$col]);
                    
                    ?>
                    <td title="<?=$row[$col]?>"><?= $reference_link ?></td>
                <?php endforeach; ?>
                <td>
                    <div class="action-links">
                        <a 
                            title="View row"
                            href="/admin/table/<?=$table['table']?>/view/<?=$row[$table['primary_key']]?>"><i class="fa-sharp fa-solid fa-eye"></i></a>
                        <a 
                            title="Edit row"
                            href="/admin/table/<?=$table['table']?>/edit/<?=$row[$table['primary_key']]?>"><i class="fa-solid fa-edit"></i></a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php


$pagination = new AppPagination();
$pagination->render($paginator);
