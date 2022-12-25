async function serviceWorker() {

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

export { serviceWorker }