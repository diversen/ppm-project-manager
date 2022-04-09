<?php

use App\AppMain;

?>
<h1>Test</h1>
<script type="module" nonce="<?=AppMain::getNonce()?>">

import {Translation} from '/lang/en/language.js';

console.log(Translation);

</script>