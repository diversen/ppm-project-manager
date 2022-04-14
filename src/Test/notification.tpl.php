<?php

use App\AppMain;

require 'templates/header.tpl.php';

?>

<div id="app"></div>

<script type="module" nonce="<?= AppMain::getNonce() ?>">

    import {NotificationUtils} from '/js/notification_utils.js?v=10';
    import {html, render} from '/js/lit-html.js';
    import {Lang} from '/js/lang.js';
    await Lang.load();

    const notify = new NotificationUtils();

    function getButtons() {

        if (!notify.isSupported()) {

            return html`
            <button id="enable" disabled>${Lang.translate('Receive notifications')}</button>
            <button id="pause" disabled>${Lang.translate('Pause notifications')}</button>
            <p id="message">${Lang.translate('Your browser does not support notifiations')}</p>`
        }

        if (Notification.permission === "granted") {

            if (notify.isNotificationPaused()) {

                return html`
                <button id="enable" disabled>${Lang.translate('Receive notifications')}</button>
                <button id="pause" @click=${pauseEvent}>${Lang.translate('Resume')}</button>
                <p id="message">${Lang.translate('Notifications Paused. You will not receive notifications')}</p>
                `

            } else {

                return html`
                <button id="enable" disabled>${Lang.translate('Receive notifications')}</button>
                <button id="pause" @click=${pauseEvent}>${Lang.translate('Pause')}</button>
                <p id="message">${Lang.translate('Notifications Enabled.')}</p>`
            }            
        }

        if (Notification.permission === 'denied') {

            return html`
                <button id="enable" disabled>${Lang.translate('Receive notifications')}</button>
                <button id="pause" disabled>${Lang.translate('Pause')}</button>
                <p id="message">${Lang.translate('You have denied notifications. Change it in your browser settings if you want to receive notifications.')}</p>`
        }

        if (Notification.permission === 'default') {
            return html`
                <button id="enable" @click=${enableEvent}>${Lang.translate('Receive notifications')}</button>
                <button id="pause" disabled>${Lang.translate('Pause')}</button>
                <p id="message">${Lang.translate('You have not enabled notifications.')}</p>`
        }
    }

    const pauseEvent = {
        handleEvent(e) {
            if (Notification.permission !== "granted") {
                return;
            }

            if (notify.isNotificationPaused()) {
                notify.resumeNotfication();
            } else {
                notify.pauseNotification();
            }

            renderApp();
        }
    }

    const enableEvent = {
        async handleEvent(e) {
            let success = await notify.enableNotification();
            if(success) {
                notify.resumeNotfication()
                notify.sendNotification(Lang.translate('Notification granted. Thanks you!'))
                renderApp();
            }
        }
    }

    const renderMain = () => html`
        <h3>${Lang.translate('Notifications')}</h3>
        ${getButtons()}
    `
 
    function renderApp () {
        render(renderMain(), document.getElementById('app'));  
    }

    renderApp();


</script>
<?php

require 'templates/footer.tpl.php';
