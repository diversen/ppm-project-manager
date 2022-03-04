<?php

use Pebble\Config;
use App\AppMain;

require 'App/templates/header_error.tpl.php'; 

?>

<h3><?=$title?></h3>

<?php

if ((new AppMain())->getConfig()->get('App.env') !== 'live'): 
    $message = "<pre>$message</pre";
endif; ?>

<?=$message?>

<?php

require 'App/templates/footer.tpl.php';
