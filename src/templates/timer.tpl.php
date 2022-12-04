<?php

use App\AppMain;

?>
<div id="timer" class="timer" >
    <span id="timer_display"></span>
    <button id="timer_start" class="button-timer button-small">Start</button>
    <button id="timer_pause" class="button-timer button-small">Pause</button>
    <button id="timer_reset" class="button-timer button-small">Reset</button>
</div>

<script src="/js/timer.js?v=?version=<?=AppMain::VERSION?>" nonce="<?=AppMain::getNonce()?>" ></script>