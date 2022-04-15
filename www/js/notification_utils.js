class NotificationUtils {

    constructor() { }

    async isSupported() {
        if ("Notification" in window) {
            let registration = await navigator.serviceWorker.ready
            if (!registration.showNotification) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }


    async isNotificationEnabled() {
        const isSupported = await this.isSupported()
        if (!isSupported) {
            return false;
        }

        if (Notification.permission === "granted") {
            return true;
        }

        return false;
    }

    // https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerRegistration/showNotification
    async sendNotification(message, options) {

        const isEnabled = await this.isNotificationEnabled();
        if (!isEnabled) {
            return false;
        }

        if (this.isNotificationPaused()) {
            return false;
        }

        let promise = navigator.serviceWorker.ready.then(function (registration) {
            registration.showNotification(message, options);
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