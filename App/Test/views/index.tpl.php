<?php

$bd = \Pebble\Config::get('App.basedir');

require 'App/templates/header.tpl.php';
require 'App/templates/flash.tpl.php';

use \Diversen\Lang;

?>

<h3><?=Lang::translate('test')?></h3>

<div class="confirm">Confirm</div>

<?php

require 'App/templates/footer.tpl.php';