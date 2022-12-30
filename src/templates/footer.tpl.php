<?php

declare (strict_types = 1);

use Diversen\Lang;

?>
        <div class="footer">
            <hr /> 
        </div>
    </div>
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

</body>
</html>