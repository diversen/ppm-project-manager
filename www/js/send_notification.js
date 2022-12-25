import { NotificationUtils } from '/js/notification_utils.js';

const notify = new NotificationUtils();

setInterval(async () => {

    let enabled = await notify.canSendNotification();

    if (enabled) {
        let options = {}
        options.tag = 'default';
        options.body = 'Great news has arrived - latest!!!'
        options.icon = '/favicon_io/android-chrome-192x192.png';
        options.actions = [];
        options.actions.push({ 'title': 'Open site', 'action': 'test' })

        // Using service worker
        notify.sendNotification('Great news!', options);
    }    
}, 5000)

const sendNotification = {}

export { sendNotification }
