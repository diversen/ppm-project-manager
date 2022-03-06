<?php

use \Diversen\Lang;

require 'App/templates/header.tpl.php'; 
require 'App/templates/flash.tpl.php'; 

?>

<h3><?=Lang::translate('You are signed in')?></h3>

<p><a href="/google/signout"><?=Lang::translate('Sign out')?></a></p>



<!--
<p>
Or <a href="https://myaccount.google.com/permissions">revoke the app on google site</a>.
</p>

<p>
You can always add it again. And your work is not lost.
</p>
-->
<?php

require 'App/templates/footer.tpl.php';




