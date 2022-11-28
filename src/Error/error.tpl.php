<?php

use App\AppMain;

?>

<h3><?=$title?></h3>

<?php

if ((new AppMain())->getConfig()->get('App.env') !== 'live'):
    $message = "<pre>$message</pre>";
endif; ?>

<?=$message?>
