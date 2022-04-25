import { Pebble } from '/js/pebble.js';
import { NotificationUtils } from '/js/notification_utils.js';
import { LocalStorageUtils } from '/js/local_storage_utils.js';

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
    menuLinks.forEach(function (v) {
        let path = v.dataset.path;
        let currentUrl = window.location.href

        if (currentUrl.includes(path)) {
            v.classList.add('active')
        }
    })
})


window.addEventListener('offline',
    function (e) {
        Pebble.removeFlashMessages();
        Pebble.setFlashMessage('You are offline. Please connect to the internet.', 'error');

    }
);

window.addEventListener('online',
    function (e) {
        Pebble.removeFlashMessages();
        Pebble.setFlashMessage('You are online again.', 'success');
    }
);

async function initServiceWorker() {

    if ('serviceWorker' in navigator) {

        const unixTime = Math.floor(Date.now() / 1000);
        navigator.serviceWorker.register('/service-worker.js').then(function (registration) {
            console.log('Service Worker Registered');

            const serviceUpdated = localStorage.getItem('service_updated');
            if (serviceUpdated) {

                const diff = unixTime - serviceUpdated;
                const hours_24 = 24 * 60 * 60;
                if (diff > hours_24) {
                    localStorage.setItem('service_updated', unixTime);
                    registration.update();
                    console.log('Service Worker Updated');
                }
            } else {
                localStorage.setItem('service_updated', unixTime);
            }
        })
    }
}

initServiceWorker()


const notify = new NotificationUtils();
const localStorageUtils = new LocalStorageUtils();

function getNotification() {
    var date = new Date();
    let dayHour = date.getHours();

    let notificationAt10 = localStorageUtils.getWithExpiry('notification_at_10');
    if (!notificationAt10 && dayHour === 10) {
        console.log('will send')
        let options = {}
        options.tag = 'default';
        options.body = 'Great news has arrived - latest!!!'
        options.icon = '/favicon_io/android-chrome-192x192.png';
        options.actions = [];
        options.actions.push({ 'title': 'Open site', 'action': 'test' })
        notify.sendNotification('Great news!', options);
    }
}


setInterval(function () {

    getNotification();
    /*
    let options = {}
    options.tag = 'default';
    options.body = 'Great news has arrived - latest!!!'
    options.icon = '/favicon_io/android-chrome-192x192.png';
    options.actions = [];
    options.actions.push({ 'title': 'Open site', 'action': 'test' })
    notify.sendNotification('Great news!', options);
    */
}, 10000)



const GlobalEvents = {}

export { GlobalEvents }
