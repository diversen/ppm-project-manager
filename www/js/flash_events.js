/**
 * Remove pre-loaded flash messages after some seconds
 */
function removeFlashMessages() {
    let elems = document.querySelectorAll('.flash-remove')
    elems.forEach(function (elem) {
        elem.remove();
    })
}

setTimeout(function () {
    removeFlashMessages();
}, 5000);

/**
 * Remove flash messages when clicked
 */
document.addEventListener("click", function (e) {
    if (e.target.classList.contains('flash')) {
        e.target.remove();
    }
})


const FlashEvents = {}
export {FlashEvents}