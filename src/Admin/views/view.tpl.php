<?php

declare(strict_types=1);

use App\Admin\HTMLUtils;

$return_to_link = $_SERVER['HTTP_REFERER'];

?>
<h3>
<?=HTMLUtils::getBreadcrumb('View', $table['table_human'])?>
</h3>
<?php

if ($error): ?>
<div class="error"><?=$error?></div>
<?php

return;

endif;


foreach ($table['columns'] as $key => $column): 
    $reference_link = HTMLUtils::getReferenceLink($column, $table['references'], $row[$column]);
    $link = $row[$column];
    if ($reference_link) {
        $link = "<a href='$reference_link'>$row[$column]</a>";
    } 
    
?>
<p><b><?=$table['columns_human'][$key]?></b>: <?=$link?></p><?php
endforeach;

$primary_key = $row[$table['primary_key']];
$edit_link = "/admin/table/$table[table]/edit/$primary_key"; 

?>
<div class="action-links">
<a href="<?=$edit_link?>"><i class="fa-solid fa-edit"></i></a>
</div>
