class NotificationUtils {

    constructor() {}

    isSupported() {
        if (!("Notification" in window)) {
            return false;
        }
        return true;
    }

    registerServiceWorker(worker) {
        navigator.serviceWorker.register(worker);
    }

    isNotificationEnabled() {

        if (!this.isSupported()) {
            return false;
        }

        if (Notification.permission === "granted") {
            return true;
        }

        return false;
    }

    sendNotification(message) {

        if (!this.isNotificationEnabled()) {
            return false;
        }

        if (this.isNotificationPaused()) {
            return false;
        }

        let promise = navigator.serviceWorker.ready.then(function (registration) {
            registration.showNotification(message);
        }).catch(function (e) {
            console.log(e)
        });
    }

    enableNotification() {

        if (!this.isSupported()) {
            return false;
        }

        if (Notification.permission === "default") {
            return Notification.requestPermission().then(function (permission) {
                if (permission === "granted") {
                    return true;
                } else {
                    return false;
                }
            });
        }
    }

    pauseNotification() {
        localStorage.setItem('notification_pause', '1');
    }

    resumeNotfication() {
        localStorage.removeItem('notification_pause');
    }

    isNotificationPaused() {
        return localStorage.getItem('notification_pause');
    }
}

export { NotificationUtils }