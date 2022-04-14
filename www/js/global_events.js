import {Pebble} from '/js/pebble.js';
import {NotificationUtils} from '/js/notification_utils.js';

/**
 * Remove pre-loaded flash messages after some seconds
 */
 document.addEventListener('DOMContentLoaded', function (e) {
    setTimeout(function () {
        Pebble.removeFlashMessages();
    }, 5000);
})

/**
 * Set active on current menu items
 */
document.addEventListener('DOMContentLoaded', function (e) {
    
    let menuLinks = document.querySelectorAll("[data-path]")
    menuLinks.forEach(function(v){
        let path = v.dataset.path;
        let currentUrl = window.location.href

        if (currentUrl.includes(path)) {
            v.classList.add('active')
        }
    })
})


window.addEventListener('offline', 
    function(e) { 
        Pebble.removeFlashMessages();
        Pebble.setFlashMessage('You are offline. Please connect to the internet.', 'error');
        
    }
);

window.addEventListener('online', 
    function(e) { 
        Pebble.removeFlashMessages();
        Pebble.setFlashMessage('You are online again.', 'success');
    }
);

if ('serviceWorker' in navigator) {
    const unixTime = Math.floor(Date.now() / 1000);

    navigator.serviceWorker.register('/service-worker.js').then(function(registration) {
        console.log('Service Worker Registered');
        const unixTime = Math.floor(Date.now() / 1000);
        
    })

    if (localStorage.getItem('service_registered')) {
        const serviceRegistered = localStorage.getItem('service_registered');
        const diff = unixTime - serviceRegistered;
        if (diff > 10) {
            navigator.serviceWorker.getRegistration('/').then(function(registration) {
                localStorage.setItem('service_registered', unixTime);
                console.log('Service Worker Updated');
                registration.update();
                console.log(registration)
            });
        }
    }
}

const notify = new NotificationUtils();

// Test
let i = 1;
setInterval(function () {
    let options = {}
    options.tag = 'default';
    options.body = 'Check this out. It may be of your interest. '
    options.actions = [];
    options.actions.push({'title': 'Open site', 'action': 'test'})
    notify.sendNotification(i * 10, options);
    i *= 10
    if (i > 10000000000000000) {
        i = 1;
    }
},  10000)

const GlobalEvents = {}

export {GlobalEvents}
