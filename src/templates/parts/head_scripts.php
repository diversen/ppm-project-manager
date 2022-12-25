<?php

declare(strict_types=1);

use App\AppMain;

?>

<!-- prevent loading of same script twice, e.g. inside imports -->
<!-- 76 procent usage of browsers support import maps at this date: 2022-12-25 -->
<script type="importmap" nonce="<?= AppMain::getNonce() ?>">
    {
      "imports": {
        "/js/pebble.js": "/js/pebble.js?v=<?= AppMain::VERSION ?>",
        "/js/js.cookie.min.js": "/js/js.cookie.min.js?v=<?= AppMain::VERSION ?>"

      }
    }
</script>
<script type="module" nonce="<?= AppMain::getNonce() ?>">
    
    import {} from '/js/online_offline.js?v=<?= AppMain::VERSION ?>';
    import {FlashEvents} from '/js/flash_events.js?v=<?= AppMain::VERSION ?>';
    import {setActiveClass} from '/js/set_active_class.js?v=<?= AppMain::VERSION ?>';
    import {serviceWorker} from '/js/service_worker.js?v=<?= AppMain::VERSION ?>';
    import {} from '/js/cookie_consent.js?v=<?=AppMain::VERSION?>';
    
    // set active class on menu links
    setActiveClass();

    // Loads service worker
    serviceWorker();
</script>