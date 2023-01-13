<?php

declare(strict_types=1);

use Diversen\Lang;

?>  
        <hr>
        <!-- <div class="footer">
            <ul>
                <li><a href="/test">Test</a></li>
                <li><a href="/test">Another Test</a></li>
            </ul>
        </div> -->
    </div>
    <div id="cookie-consent">
        <p class="block">
            <span><b><?= Lang::translate('Notice') ?></b></span>:
            <?= Lang::translate('This website may use non-essential cookies for statistical usage and improving experience.'); ?> 
            <br>
            <?=Lang::translate('You may accept or reject any non-essential cookies.') ?> 
            <a href="/account/terms/privacy-policy" target="_blank"><?= Lang::translate('Read more') ?></a>.
        </p>
        <p class="block">
            <button id="cookie-accept" class="accept" type="button"><?=Lang::translate('Accept')?></button>
            <button id="cookie-reject" class="reject" type="button"><?=Lang::translate('Reject')?></button>
        </p>
    </div>

</body>
</html>