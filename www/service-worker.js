const version = 'v1.1.4';

self.addEventListener('install', event => {
    console.log(`${version} installing …`);

    self.skipWaiting();

    console.log('skipped waiting');

    // Cache something
    // event.waitUntil(
    //     caches.open('static-v1').then(cache => cache.add('/cat.svg'))
    // );
});

self.addEventListener('activate', event => {
    console.log(`${version} now ready to handle fetches!`);
});

self.addEventListener('notificationclick', function (event) {

    console.log(`${version}. Notification click: service-worker.js`);

    if (event.action === 'test') {
        var url = '/settings'
    } else {
        var url = '/overview'
    }

    event.notification.close(); // Android needs explicit close.
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            // Check if there is already a window/tab open with the target URL
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                // If so, just focus it.
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            // If not, then open the target URL in a new window/tab.
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});