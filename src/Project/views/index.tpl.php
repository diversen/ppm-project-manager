<?php

declare(strict_types=1);

use Diversen\Lang;
use App\Utils\AppPagination;
use Pebble\Pagination\PaginationUtils;
use App\Utils\DateUtils;

?>

<h3 class="sub-menu"><?= Lang::Translate('Projects') ?></h3>

<div class="action-links">
    <a href="/project/add" title="<?= Lang::translate('Add new project') ?>"><?= Lang::translate('Add project') ?></a>
</div>

<?php

function render_project($project)
{
    $date_utils = new DateUtils();

    // Stored in UTC, convert to user local timezone
    $updated = $date_utils->getUserDateFormatFromUTC($project['updated'], 'd/m/Y'); ?>
    <tr>
        <td class="td-overflow"><a title="<?= $project['note'] ?>" href='/project/view/<?= $project['id'] ?>'><?= $project['title'] ?></a></td>
        <td><?= $updated ?></td>
        <td class="xs-hide"><?= $project['project_time_total_human'] ?></td>
        <td>
            <div class="action-links">
                <a href="/project/edit/<?= $project['id'] ?>" title="<?= Lang::translate('Edit project') ?>"><?= get_icon('edit') ?></a>
                <a href="/task/add/<?= $project['id'] ?>" title="<?= Lang::translate('Add new task to project') ?>"><?= get_icon('add') ?></a>
            </div>
        </td>
    </tr>
    <?php
}

function render_projects($projects, $default_order_by)
{
    $pagination_utils = new PaginationUtils($default_order_by, 'project');

    if (empty($projects)) { ?>
        <p><?= Lang::translate('Your have no projects yet') ?></p>
    <?php
    } else { ?>
        <table>
            <thead>
                <tr>
                    <td>
                        <a href="<?= $pagination_utils->getAlterOrderUrl('p.title') ?>">
                            <?= Lang::translate('Title') ?> <?= $pagination_utils->getCurrentDirectionArrow('p.title') ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= $pagination_utils->getAlterOrderUrl('p.updated') ?>">
                        <?= Lang::translate('Updated') ?> <?= $pagination_utils->getCurrentDirectionArrow('p.updated') ?></a>
                    </td>
                    <td class="xs-hide">
                        <a href="<?= $pagination_utils->getAlterOrderUrl('project_time_total') ?>">
                        <?= Lang::translate('Time used') ?> <?= $pagination_utils->getCurrentDirectionArrow('project_time_total') ?></a>
                    </td>
                    <td>

                    </td>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($projects as $project) {
                    render_project($project);
                }
                ?>
            </tbody>
        </table>
    <?php

    }
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

render_projects($projects, $default_order_by);

$pagination = new AppPagination();
$pagination->render($paginator);

if (isset($inactive_link)) {
    render_projects_inactive_link();
}
