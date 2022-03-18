<?php

declare(strict_types=1);

use Diversen\Lang;

require 'App/templates/header.tpl.php';

?>

<h3 class="sub-menu"><?= Lang::Translate('Projects') ?></h3>

<div class="action-links">
    <a href="/project/add"><?= Lang::translate('Add project') ?></a>
</div>

<?php

function render_projects($projects)
{
    if (empty($projects)) :
        echo "<p>" . Lang::translate('Your have no projects yet') . "</p>"; else : ?>
        <table>
            <thead>
                <tr>
                    <td><?= Lang::translate('Title') ?></td>
                    <td><?= Lang::translate('Date') ?> </td>
                    <td class="xs-hide"><?= Lang::translate('Time used') ?></td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($projects as $project) :

                    $created = date('d/m/Y', strtotime($project['created'])); ?>
                    <tr>
                        <td class="td-overflow"><a title="<?= $project['note'] ?>" href='/project/view/<?= $project['id'] ?>'><?= $project['title'] ?></a></td>
                        <td><?= $created ?></td>
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

function render_projects_inactive_link($inactive)
{
    if (!empty($inactive)) : ?>
        <div class="action-links">
            <a href='/project?inactive=1'><?= Lang::translate('View inactive projects') ?></a>
        </div>
    <?php
    endif;
}

function render_projects_total_time($total_time_human)
{
    ?>
    <div>
        <p><?= Lang::translate('Total time used on all projects') ?> <?= $total_time_human ?></p>
    </div>
<?php
}


if (isset($_GET['inactive'])) {
    render_projects($inactive);
} else {
    render_projects($projects);
    render_projects_inactive_link($inactive);
}

render_projects_total_time($total_time_human);


require 'App/templates/footer.tpl.php';
