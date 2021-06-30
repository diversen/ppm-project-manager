<div id="timer_display"></div>
<div>
    <button id="timer_start" href="#">Start</button>
    <button id="timer_pause" href="#">Pause</button>
    <button id="timer_reset" href="#">Reset</button>
</div>


<script>
    function Timer() {

        var startTime;
        var t;
        var now;
        var runningTime = 0;
        var timer_state_init = localStorage.getItem('timer_state');

        if (!timer_state_init) {
            localStorage.setItem('timer_state', 'stopped');
        }

        if (!localStorage.getItem('timer_elapsed')) {
            localStorage.setItem('timer_elapsed', 0);
        }

        function startTimeCounter() {
            now = Math.floor(Date.now() / 1000);
            runningTime = now - startTime;
            localStorage.setItem('timer_elapsed', runningTime);
            displayTime(runningTime)
        }

        function displayTime(diff) {
            var m = Math.floor(diff / 60);
            var s = Math.floor(diff % 60);
            m = checkTime(m);
            s = checkTime(s);
            var time_str = new Date(diff * 1000).toISOString().substr(11, 8)
            // document.getElementById("timer_display").innerHTML = m + ":" + s;
            document.getElementById("timer_display").innerHTML = time_str;
        }

        function checkTime(i) {
            if (i < 10) {
                i = "0" + i
            };
            return i;
        }

        timer_start = document.getElementById('timer_start');
        timer_start.addEventListener('click', function (e) {
            startTimer();
        });

        let timer_pause = document.getElementById('timer_pause');
        timer_pause.addEventListener('click', function (e) {

            let timer_state = localStorage.getItem('timer_state')
            if (timer_state == 'running') {
                pauseTimer();
            }

            if (timer_state == 'paused') {
                resumeTimer();
            }
        });

        timer_reset = document.getElementById('timer_reset');
        timer_reset.addEventListener('click', function (e) {
            resetTimer();
        });

        function startTimer() {
            timer_start.setAttribute('disabled', true)
            timer_pause.removeAttribute('disabled')
            timer_reset.removeAttribute('disabled')
            localStorage.setItem('timer_state', 'running');
            startTime = Math.floor(Date.now() / 1000);
            t = setInterval(startTimeCounter, 500);

        }

        function pauseTimer() {
            timer_start.setAttribute('disabled', true)
            localStorage.setItem('timer_state', 'paused')
            timer_elapsed = localStorage.getItem('timer_elapsed');
            displayTime(timer_elapsed);
            clearInterval(t);
            timer_pause.innerText = 'Resume'
        }

        function resumeTimer() {
            timer_start.setAttribute('disabled', true)
            localStorage.setItem('timer_state', 'running')
            timer_elapsed = localStorage.getItem('timer_elapsed');

            startTime = Math.floor(Date.now() / 1000) - timer_elapsed;
            t = setInterval(startTimeCounter, 500);
            timer_pause.innerText = 'Pause'
        }

        function resetTimer() {
            timer_start.removeAttribute('disabled')
            timer_reset.setAttribute('disabled', true);
            timer_pause.setAttribute('disabled', true);

            localStorage.setItem('timer_state', 'stopped');
            localStorage.setItem('timer_elapsed', 0);

            document.getElementById("timer_display").innerHTML = ''

            clearInterval(t);
            displayTime(0);
        }


        if (timer_state_init == 'stopped') {
            resetTimer();
        }

        if (timer_state_init == 'running') {
            resumeTimer();
        }

        if (timer_state_init == 'paused') {
            pauseTimer()
        }
    }

    Timer();

</script>