<?php

use Diversen\Lang;

require 'templates/header.tpl.php';
require 'templates/flash.tpl.php';

?>

<h3><?=Lang::translate('Click the image and sign-in using google')?></h3>

<div class="row">
    <p><a href="<?=$auth_url?>"><img src="/assets/google-signin.png" /></a></p>
</div>

<?php

require 'templates/footer.tpl.php';
