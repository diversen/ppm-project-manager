<?php

declare(strict_types=1);

use App\Admin\HTMLUtils;

$return_to_link = $_SERVER['HTTP_REFERER'] ?? '/admin';

?>
<h3 class="sub-menu">
<?=HTMLUtils::getBreadcrumb('View', $table['table_human'])?>
</h3>
<?php

if ($error): ?>
<div class="error"><?=$error?></div>
<?php

return;

endif;

?>
<div style="display: table">

<?php


foreach ($table['columns'] as $key => $column):
    $reference_link = HTMLUtils::getReferenceLink($column, $table['references'], $row[$column]);
    $link = $row[$column];
    if ($reference_link) {
        $link = "<a href='$reference_link'>$row[$column]</a>";
    }

?>
<p><b><?=$table['columns_human'][$key]?></b>: <?=$link?></p><?php
endforeach;

?>
</div>
