<?php
 
use App\AppMain;

?>
<script type="module" nonce="<?= AppMain::getNonce() ?>">
    import {
        GlobalEvents
    } from '/js/global_events.js?version=<?= AppMain::VERSION ?>';
</script>