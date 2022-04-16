<?php

declare(strict_types=1);

use Diversen\Lang;
use App\Utils\AppPagination;
use App\Utils\AppPaginationUtils;

require 'templates/header.tpl.php';

?>

<h3 class="sub-menu"><?= Lang::Translate('Projects') ?></h3>

<div class="action-links">
    <a href="/project/add" title="<?=Lang::translate('Add new project')?>"><?= Lang::translate('Add project') ?></a>
</div>

<?php

function render_projects($projects)
{
    $pagination_utils = new AppPaginationUtils(['updated' => 'DESC', 'title' => 'DESC']);

    if (empty($projects)) : ?>
        <p><?=Lang::translate('Your have no projects yet')?></p><?php

    else : ?>
        <table>
            <thead>
                <tr>
                    <td><a href="<?=$pagination_utils->getAlterOrderUrl('title')?>">
                        <?= Lang::translate('Title') ?> <?=$pagination_utils->getCurrentDirectionArrow('title')?></a>
                    </td>
                    <td><a href="<?=$pagination_utils->getAlterOrderUrl('updated')?>">
                        <?= Lang::translate('Updated') ?> <?=$pagination_utils->getCurrentDirectionArrow('updated')?></a> </td>
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
                                <a href="/project/edit/<?= $project['id'] ?>" title="<?= Lang::translate('Edit task') ?>"><?=get_icon('edit')?></a>
                                <a href="/task/add/<?= $project['id'] ?>" title="<?=Lang::translate('Add new task to project')?>"><?=get_icon('add')?></a>
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

$pagination = new AppPagination();
$pagination->render($paginator);

if (isset($inactive_link)) {
    render_projects_inactive_link();
}

require 'templates/footer.tpl.php';
