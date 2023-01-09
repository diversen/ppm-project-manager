<?php

declare(strict_types=1);

?>
<h3 class="sub-menu">
Admin
</h3>
<ul><?php
foreach ($tables as $key => $table) { ?>
<li><a href="/admin/table/<?=$key?>"><?=$table['table_human']?></a></li>
<?php

}

?>
</ul>