<?php

use App\AppMain;

?>
<h1>Translate</h1>
<p id="message"></p>
<script type="module" nonce="<?= (new AppMain())->getNonce(); ?>">

    import {Lang} from '/js/lang.js';
    await Lang.load();

    let translated_str = Lang.translate('Activity this week: <span class="notranslate">{week_user_total}</span>', {'week_user_total': 100});

    let message = document.getElementById('message');
    message.innerHTML = translated_str;
    console.log(translated_str);
    
</script>