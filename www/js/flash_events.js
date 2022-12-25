/**
 * Remove pre-loaded flash messages after some seconds
 */
function removeFlashMessages() {
    let elems = document.querySelectorAll('.flash-remove')
    elems.forEach(function (elem) {
        elem.remove();
    })
}


document.addEventListener('DOMContentLoaded', function (e) {
    setTimeout(function () {
        removeFlashMessages();
    }, 5000);
})

const FlashEvents = {}
export {FlashEvents}