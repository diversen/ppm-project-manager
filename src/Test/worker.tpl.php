<?php

use App\AppMain;

?>

<h3>Worker</h3>

<p id="message"></p>
<script type="module" nonce="<?= AppMain::getNonce(); ?>">

    const message = document.getElementById('message');
    if (window.SharedWorker) {
        var myWorker = new SharedWorker("/js/worker.js");

        myWorker.port.postMessage([10, 20]);
        myWorker.port.onmessage = function(e) {
            message.innerText = e.data;
        }
    }

</script>