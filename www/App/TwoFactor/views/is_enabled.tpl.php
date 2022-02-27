<?php

use Diversen\Lang;

require 'App/templates/header.tpl.php';

?>
<h3 class="sub-menu"><?=Lang::translate('Enable two factor authentication')?></h3>

<p><?=Lang::translate('Two factor is already enabled')?></p>
<p><a href="/2fa/recreate"><?=Lang::translate('Get a new QR code')?></p>

<?php

require 'App/templates/footer.tpl.php';