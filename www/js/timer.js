
if (!sessionStorage.getItem('timer_toggle_state')) {
    sessionStorage.setItem('timer_toggle_state', 'hidden');
}

var timer_elem = document.getElementById('timer');

var timer_toggle_state = sessionStorage.getItem('timer_toggle_state')
if (timer_toggle_state == 'display') {
    timer_elem.style.display = "block";
}

var timer_toggle = document.getElementById('timer_toggle');
timer_toggle.addEventListener('click', function (e) {
    if (timer_elem.style.display === "none" || timer_elem.style.display === "") {
        sessionStorage.setItem('timer_toggle_state', 'display')
        timer_elem.style.display = "block";
    } else {
        sessionStorage.setItem('timer_toggle_state', 'hidden')
        timer_elem.style.display = "none";
    }
})


function Timer() {

    var startTime;
    var intervalId;
    var now;
    var runningTime = 0;
    var timer_state_init = sessionStorage.getItem('timer_state');

    if (!timer_state_init) {
        sessionStorage.setItem('timer_state', 'stopped');
    }

    if (!sessionStorage.getItem('timer_elapsed')) {
        sessionStorage.setItem('timer_elapsed', 0);
    }

    function startTimeCounter() {
        now = Math.floor(Date.now() / 1000);
        runningTime = now - startTime;
        sessionStorage.setItem('timer_elapsed', runningTime);
        displayTime(runningTime)
    }

    function displayTime(diff) {
        var m = Math.floor(diff / 60);
        var s = Math.floor(diff % 60);
        m = checkTime(m);
        s = checkTime(s);
        var time_str = new Date(diff * 1000).toISOString().substr(11, 8)
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
        console.log('started clock')
        startTimer();
    });

    let timer_pause = document.getElementById('timer_pause');
    timer_pause.addEventListener('click', function (e) {

        let timer_state = sessionStorage.getItem('timer_state')
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
        sessionStorage.setItem('timer_state', 'running');
        startTime = Math.floor(Date.now() / 1000);
        intervalId = setInterval(startTimeCounter, 500);

    }

    function pauseTimer() {
        timer_start.setAttribute('disabled', true)
        sessionStorage.setItem('timer_state', 'paused')
        timer_elapsed = sessionStorage.getItem('timer_elapsed');
        displayTime(timer_elapsed);

        clearInterval(intervalId);
        timer_pause.innerText = 'Resume'
    }

    function resumeTimer() {
        timer_start.setAttribute('disabled', true)
        sessionStorage.setItem('timer_state', 'running')
        timer_elapsed = sessionStorage.getItem('timer_elapsed');

        startTime = Math.floor(Date.now() / 1000) - timer_elapsed;
        intervalId = setInterval(startTimeCounter, 500);
        timer_pause.innerText = 'Pause'
    }

    function resetTimer() {
        timer_start.removeAttribute('disabled');
        timer_reset.setAttribute('disabled', true);
        timer_pause.setAttribute('disabled', true);
        timer_pause.innerText = 'Pause';

        sessionStorage.setItem('timer_state', 'stopped');

        document.getElementById("timer_display").innerHTML = ''

        clearInterval(intervalId);
        runningTime = 0
        sessionStorage.setItem('timer_elapsed', 0);

        displayTime(0);
    }

    if (!timer_state_init) {
        resetTimer();
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