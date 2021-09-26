import {Pebble} from '/App/js/pebble.js';

/**
 * Remove pre-loaded flash messages after some seconds
 */
 document.addEventListener('DOMContentLoaded', function (e) {
    setTimeout(function () {
        Pebble.removeFlashMessages();
    }, 5000);
})

/**
 * Set active on current menu items
 */
document.addEventListener('DOMContentLoaded', function (e) {
    
    let menuLinks = document.querySelectorAll("[data-path]")
    menuLinks.forEach(function(v){
        let path = v.dataset.path;
        let currentUrl = window.location.href

        if (currentUrl.includes(path)) {
            v.classList.add('active')
        }
    })
})

const GlobalEvents = {}

export {GlobalEvents}