<?php

use App\AppMAin;

?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?=$analytics_tag?>" nonce="<?= AppMain::getNonce() ?>"></script>
<script type="module" nonce="<?= AppMain::getNonce() ?>">
    import Cookies from '/js/js.cookie.min.js?v=<?= AppMain::VERSION ?>';

    var cookieConsentAnswer = Cookies.get('cookie-consent');
    if (cookieConsentAnswer === 'enabled') {

        // Almost anyone serves google analytics without consent,
        // but this is not doing it
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', '<?=$analytics_tag?>');
    }
    
</script>