<?php

use App\AppMain;

?>
<script type="module" nonce="<?= AppMain::getNonce() ?>">
    import {} from '/js/online_offline.js?version=<?= AppMain::VERSION ?>';
    import {FlashEvents} from '/js/flash_events.js?version=<?= AppMain::VERSION ?>';
    import {setActiveClass} from '/js/set_active_class.js?version=<?= AppMain::VERSION ?>';
    import {serviceWorker} from '/js/service_worker.js?version=<?= AppMain::VERSION ?>';

    
    // set active class on menu links
    setActiveClass();

    // Loads service worker
    serviceWorker();
</script>