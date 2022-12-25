import Cookies from '/js/js.cookie.min.js';

var cookieConsentAnswer = Cookies.get('cookie-consent');
if (!cookieConsentAnswer) {
    const cookieConsent = document.getElementById('cookie-consent');
    cookieConsent.style.display = 'block';
}

const cookieAccept = document.getElementById('cookie-accept');
const cookieReject = document.getElementById('cookie-reject');
const cookieConsentDays = 182

cookieAccept.addEventListener('click', () => {
    Cookies.set('cookie-consent', 'enabled', { expires: cookieConsentDays });
    const cookieConsent = document.getElementById('cookie-consent');
    cookieConsent.style.display = 'none';
})

cookieReject.addEventListener('click', () => {
    Cookies.set('cookie-consent', 'disabled', { expires: cookieConsentDays });
    const cookieConsent = document.getElementById('cookie-consent');
    cookieConsent.style.display = 'none';
})

export {  }