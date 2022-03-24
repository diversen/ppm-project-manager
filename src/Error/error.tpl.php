<?php

use App\AppMain;

require 'templates/header_error.tpl.php';

?>

<h3><?=$title?></h3>

<?php

if ((new AppMain())->getConfig()->get('App.env') !== 'live'):
    $message = "<pre>$message</pre";
endif; ?>

<?=$message?>

<?php

require 'templates/footer.tpl.php';
