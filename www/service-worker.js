self.addEventListener('notificationclick', function (event) {

    if (event.action === 'test') {
        var url = '/settings'
    } else {
        var url = '/overview'
    }


    console.log(event);
    // let url = 'https://ppm.10kilobyte.com/overview';
    console.log('click sw 5')
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