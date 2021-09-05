<?php

use Pebble\Config;

require 'App/templates/header_error.tpl.php'; 

?>

<h3><?=$title?></h3>

<?php

if (Config::get('App.env') !== 'live'): 
    $message = "<pre>$message</pre";
endif; ?>

<?=$message?>

<?php

require 'App/templates/footer.tpl.php';
