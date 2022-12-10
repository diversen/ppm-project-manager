<?php

declare(strict_types=1);

?>
<h3>Admin</h3>
<ul><?php
foreach ($tables as $key => $table) { ?>
<li><a href="/admin/table/<?=$key?>"><?=$table['table_human']?></a></li>
<?php

}

?>
</ul>