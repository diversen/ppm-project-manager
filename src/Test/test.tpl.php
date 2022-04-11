<?php

use App\AppMain;

?>
<h1>Test</h1>
<script type="module" nonce="<?=AppMain::getNonce()?>">

import {Translation} from '/js/lang/da/language.js';
import Cookies from '/js/js.cookie.min.js';

function replace (str, substitutions) {
    for (const str in substitutions) {
        str = str.replace('{' + key )
            console.log(str)
    }

    console.log(str, substitutions);
}

async function translate (str, substitutions) {
    let language = Cookies.get('language');
    if (!language) language = 'en';

    const modulePath = '/js/lang/' + language + '/language.js';
    let {Translation} = await import(modulePath)
    // console.log(module);
    let translatedStr = Translation[str];
    if (!translatedStr) {

    }

    replace(translatedStr, substitutions )
}

await translate('Activity this week: <span class="notranslate">{week_user_total}</span>', {'week_user_total': 100});


</script>