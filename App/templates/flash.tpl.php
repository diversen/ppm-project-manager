<?php

$flash_messages = \Pebble\Flash::getMessages();

?>

<div class="flash-messages">

<?php

foreach($flash_messages as $message): 

    // The flash-remove class indicates if the flash message will be removed with JS after som seconds.
    // If this class is not present then the flash message will NOT be removed
    $remove_class = '';
    if (isset($message['options']['flash_remove'])) {
        $remove_class = ' flash-remove ';
    }

?>
<div class="flash flash-<?=$message['type']?> <?=$remove_class?>"><?=$message['message']?></div>
<?php

endforeach;

?>
</div>