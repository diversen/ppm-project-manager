<?php

declare(strict_types=1);

use Diversen\Lang;
use App\Pagination;
use App\PaginationUtils;
use Pebble\URL;

require 'templates/header.tpl.php';

?>

<h3 class="sub-menu"><?= Lang::Translate('Projects') ?></h3>

<div class="action-links">
    <a href="/project/add"><?= Lang::translate('Add project') ?></a>
</div>

<?php

function get_order_link($field, $order_by) {
    $order_by = $_GET['order_by'] ?? null;
    $direction = $_GET['direction'] ?? null;
    
    // Defaults
    $query['order_by'] = $field;
    $query['direction'] = 'ASC';
    
    // Already ordering by field. Switch directions
    if ($order_by === $field) {
        if ($direction === 'ASC') {
            $query['direction'] = 'DESC';
        } else {
            $query['direction'] = 'ASC';
        }
    }

    // Add current page
    $query['page'] = URL::getQueryPart('page') ?? '1';

    $route = strtok($_SERVER["REQUEST_URI"], '?');
    return  $route . '?' . http_build_query($query);
}

function render_projects($projects)
{   
    
    $pagination_utils = new PaginationUtils([], 'title');
    
    if (empty($projects)) :
        echo "<p>" . Lang::translate('Your have no projects yet') . "</p>"; 
    else : ?>
        <table>
            <thead>
                <tr>
                    <td><a href="<?=$pagination_utils->getOrderByUrl('title')?>">
                        <?= Lang::translate('Title') ?> <?=$pagination_utils->getCurrentDirectionArrow('title')?></a>
                    </td>
                    <td><a href="<?=$pagination_utils->getOrderByUrl('updated')?>">
                        <?= Lang::translate('Updated') ?><?=$pagination_utils->getCurrentDirectionArrow('updated')?></a> </td>
                    <td class="xs-hide"><?= Lang::translate('Time used') ?></td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($projects as $project) :

                    $updated = date('d/m/Y', strtotime($project['updated'])); ?>
                    <tr>
                        <td class="td-overflow"><a title="<?= $project['note'] ?>" href='/project/view/<?= $project['id'] ?>'><?= $project['title'] ?></a></td>
                        <td><?= $updated ?></td>
                        <td class="xs-hide"><?= $project['project_time_total_human'] ?></td>
                        <td>
                            <div class="action-links">
                                <a href="/project/edit/<?= $project['id'] ?>"><?= Lang::translate('Edit') ?></a>
                                <a href="/task/add/<?= $project['id'] ?>"><?= Lang::translate('New') ?></a>
                            </div>
                        </td>
                    </tr>
                <?php

                endforeach; ?>
            </tbody>
        </table>
    <?php

    endif;
}

function render_projects_inactive_link()
{ ?>
    <div class="action-links">
        <a href='/project/inactive'><?= Lang::translate('View inactive projects') ?></a>
    </div>
    <?php

}

function render_projects_total_time($total_time_human)
{
    ?>
    <div>
        <p><?= Lang::translate('Total time used on all projects') ?> <?= $total_time_human ?></p>
    </div>
<?php
}

render_projects($projects);

$pagination = new Pagination();
$pagination->render($paginator);

if (isset($inactive_link)) {
    render_projects_inactive_link();
}

require 'templates/footer.tpl.php';
