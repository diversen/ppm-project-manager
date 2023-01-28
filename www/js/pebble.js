class Pebble {

    static setFlashMessage(str, type, remove_after) {
        var messageElem = document.querySelector(".flash-messages");
        messageElem.innerHTML = '';

        if (!type) {
            type = 'notice';
        }

        let class_random = '';
        if (remove_after) {
            class_random = 'random_' + (Math.random() + 1).toString(36).substring(2);
            setTimeout(function () {
                let elem = document.querySelector('.' + class_random)
                if (elem) {
                    elem.remove();
                }
            }, remove_after)
        }

        var html = `<div class="flash flash-${type} ${class_random}">${str}</div>`;
        messageElem.insertAdjacentHTML('afterbegin', html);
        messageElem.scrollIntoView();
    }

    static toggleVisible(elem) {
        if (elem.style.visibility === 'visible') {
            elem.style.visibility = 'hidden';
        } else {
            elem.style.visibility = 'visible';
        }
    }

    static toggleHide(elem) {
        if (elem.style.display === "none") {
            elem.style.display = "block";
        } else {
            elem.style.display = "none";
        }
    }

    static logFormdata(data) {
        for (var p of data) {
            let name = p[0];
            let value = p[1];
            console.log(name, value)
        }
    }

    static show(elem) {
        elem.style.display = 'block';
    };

    static hide(elem) {
        elem.style.display = 'none';
    };

    /**
     * Get a Path segment of the window.location.pathname
     */
    static getPathPart(num, path) {
        if (!path) {
            path = window.location.pathname;
        }
        var ary = path.split('/');
        ary.shift();
        return ary[num];
    }

    /**
     * Get a query variable from current windown.location.search
     */
    static getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split('&');
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            if (decodeURIComponent(pair[0]) == variable) {
                return decodeURIComponent(pair[1]);
            }
        }
    }

    /**
     * Post formdata async. Accepts JSON as response
     */
    static async asyncPost(url, formData) {
        const rawResponse = await fetch(url, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
            },
            body: formData
        }).then(function (response) {
            return response.json()
        }).then(function (response) {
            return response;
        });

        return rawResponse;
    }

    /**
     * Async request. Accept JSON as response
     */
    static async asyncRequest(url, formData, method) {
        const rawResponse = await fetch(url, {
            method: method,
            headers: {
                'Accept': 'application/json',
            },
            body: formData
        }).then(function (response) {
            return response.json()
        }).then(function (response) {
            return response;
        });

        return rawResponse;
    }

    /**
     * Async GET request. Accept JSON as response 
     */
    static async asyncGet(url) {
        const rawResponse = await fetch(url, {
            method: 'get',
            headers: {
                'Accept': 'application/json',
            },
        }).then(function (response) {
            return response.json()
        }).then(function (response) {
            return response;
        });

        return rawResponse;
    }

    /**
     * Sends a Error to an endpont (for logging)
     */
    static async asyncPostError(endpoint, error) {
        const error_data = new FormData();
        error_data.set('error', error);
        return Pebble.asyncPost(endpoint, error_data);
    }

    static redirect(url) {
        // Add to window history
        // Another option is
        // window.location.assign(url)
        window.location.replace(url)
    }
}

export { Pebble }