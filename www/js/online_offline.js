import { Pebble } from '/js/pebble.js';


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

export {  }