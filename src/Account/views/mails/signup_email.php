<?php

use Diversen\Lang;

?>
<?=Lang::translate('Hi')?>,

<?=Lang::translate('You have connected with the following site and requested an account')?>:

<?=$site_name?>

<?=Lang::translate('In order to activate your account, you just have to press the following link or copy and paste into your browsers URL line')?>:

<?=$activation_url?>


<?=Lang::translate('Kind Regards')?> <?=$site_name?>