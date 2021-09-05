<?php

use \Diversen\Lang;

?>
<?=Lang::translate('Hi')?>,

<?=Lang::translate('You have connected with the following site because you have lost your password')?>: <?=$site_name?> 

<?=Lang::translate('If you have not requested a new password then you can just delete this message')?>.

<?=Lang::translate('In order to reset your password you just have to press the following link - or copy and paste into your browsers URL line')?>:

<?=$activation_url?>


<?=Lang::translate('Kind Regards')?> <?=$site_name?>