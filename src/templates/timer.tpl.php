<?php

use App\AppMain;

?>
<div id="timer" class="timer" >
    <span id="timer_display"></span>
    <button id="timer_start" class="button-timer button-small" href="#">Start</button>
    <button id="timer_pause" class="button-timer button-small" href="#">Pause</button>
    <button id="timer_reset" class="button-timer button-small" href="#">Reset</button>
</div>

<script src="/js/timer.js?v=?version=<?=AppMain::VERSION?>" ></script>