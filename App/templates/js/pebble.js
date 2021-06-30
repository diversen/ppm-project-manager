var setFlashMessage = function (str, type) {
    var messageElem = document.querySelector(".flash-messages");
    messageElem.innerHTML = '';

    if (!type) {
        type = 'notice';
    }

    var html = `<div class="flash flash-${type}"> ${str} </div>`;
    messageElem.insertAdjacentHTML('afterbegin', html);
    messageElem.scrollIntoView();
}

var toggleVisible = function (elem) {
    if (elem.style.visibility === 'visible') {
        elem.style.visibility = 'hidden';
    } else {
        elem.style.visibility = 'visible';
    }
}

var toggleHide = function (elem) {
    if (elem.style.display === "none") {
        elem.style.display = "block";
    } else {
        elem.style.display = "none";
    }
}

var logFormdata = function (data) {
    for (var p of data) {
        let name = p[0];
        let value = p[1];
        console.log(name, value)
    }
} 

// Show an element
var show = function (elem) {
    elem.style.display = 'block';
};

// Hide an element
var hide = function (elem) {
    elem.style.display = 'none';
};

// Toggle element visibility
var toggleDisplay = function (elem) {

    // If the element is visible, hide it
    if (window.getComputedStyle(elem).display === 'block') {
        hide(elem);
        return;
    }

    // Otherwise, show it
    show(elem);

};

var getPathPart = function (num) {
    var path = window.location.pathname;
    var ary = path.split('/');
    ary.shift();
    return ary[num];
}

function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
}

async function asyncPost(url, formData) {
    const rawResponse = await fetch(url, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            // 'Content-Type': 'application/json'
        },
        body: formData
    }).then(function (response) {
        return response.json()
    }).then(function (response) {
        return response;
    });
    
    return rawResponse;
}

async function asyncRequest(url, formData, method) {
    const rawResponse = await fetch(url, {
        method: method,
        headers: {
            'Accept': 'application/json',
            // 'Content-Type': 'application/json'
        },
        body: formData
    }).then(function (response) {
        return response.json()
    }).then(function (response) {
        return response;
    });
    
    return rawResponse;
}

function removeFlashMessages() {
    let elems = document.querySelectorAll('.flash-remove')
    elems.forEach(function (elem) {
        elem.remove();
    })
}

var Pebble = {
    getPathPart: getPathPart,
    setFlashMessage: setFlashMessage,
    toggleVisible: toggleVisible,
    toggleHide: toggleHide,
    toggleDisplay: toggleDisplay,
    getQueryVariable: getQueryVariable,
    asyncPost: asyncPost,
    logFormdata: logFormdata,
    asyncRequest: asyncRequest,
}

/**
 * DOMContentLoaded events
 */

/**
 * Remove flash messages after some seconds
 */
document.addEventListener('DOMContentLoaded', function (e) {
    setTimeout(function() {
        removeFlashMessages();
    }, 5000);
})