<?php

use Diversen\Lang;

require 'App/templates/header.tpl.php';
require 'App/templates/flash.tpl.php';

?>

<h3><?=Lang::translate('Click the image and sign-in using google')?></h3>

<div class="row">
    <p><a href="<?=$auth_url?>"><img src="/App/templates/assets/google-signin.png" /></a></p>
</div>

<?php

require 'App/templates/footer.tpl.php';
