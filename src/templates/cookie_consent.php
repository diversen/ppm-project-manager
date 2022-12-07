<?php

use Diversen\Lang;
use App\AppMain;

?>
<div id="cookie-consent">
    <p>
        <span><b><?= Lang::translate('Notice') ?></b></span>:
        <?= Lang::translate('This website may use non-essential cookies for statistical usage and improving experience.'); ?> 
        <br />
        <?=Lang::translate('You may accept or reject any non-essential cookies.') ?> 
        <a href="/account/terms/privacy-policy" target="_blank"><?= Lang::translate('Read more') ?></a>.
    </p>
    <p>
        <button id="cookie-accept" class="accept" type="button"><?=Lang::translate('Accept')?></button>
        <button id="cookie-reject" class="reject" type="button"><?=Lang::translate('Reject')?></button>
    </p>
</div>
<script type="module" nonce="<?=AppMain::getNonce()?>" >
    import Cookies from '/js/js.cookie.min.js?v=<?=AppMain::VERSION?>';

    var cookieConsentAnswer = Cookies.get('cookie-consent');
    if (!cookieConsentAnswer) {
        const cookieConsent = document.getElementById('cookie-consent');
        cookieConsent.style.display = 'block';
    }

    const cookieAccept = document.getElementById('cookie-accept');
    const cookieReject = document.getElementById('cookie-reject');
    const cookieConsentDays = 182
    
    cookieAccept.addEventListener('click', () => {
        Cookies.set('cookie-consent', 'enabled', { expires: cookieConsentDays });
        const cookieConsent = document.getElementById('cookie-consent');
        cookieConsent.style.display = 'none';
    })

    cookieReject.addEventListener('click', () => {
        Cookies.set('cookie-consent', 'disabled', { expires: cookieConsentDays });
        const cookieConsent = document.getElementById('cookie-consent');
        cookieConsent.style.display = 'none';
    })

</script>