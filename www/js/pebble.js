class Pebble {

    /**
     * Sets a flash message
     */
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
                console.log(class_random)
                document.querySelector('.' + class_random).remove();
            }, remove_after)
        }

        var html = `<div class="flash flash-${type} ${class_random}">${str}</div>`;
        messageElem.insertAdjacentHTML('afterbegin', html);
        messageElem.scrollIntoView();
    }

    /**
     * Toggle visibility of an element between visible and hidden
     */
    static toggleVisible(elem) {
        if (elem.style.visibility === 'visible') {
            elem.style.visibility = 'hidden';
        } else {
            elem.style.visibility = 'visible';
        }
    }

    /**
     * Toggle display of en element between 'none' and 'block' 
     */
    static toggleHide(elem) {
        if (elem.style.display === "none") {
            elem.style.display = "block";
        } else {
            elem.style.display = "none";
        }
    }

    /**
     * Logs FormData object
     */
    static logFormdata(data) {
        for (var p of data) {
            let name = p[0];
            let value = p[1];
            console.log(name, value)
        }
    }

    /**
     * Set elem display to block
     */
    static show(elem) {
        elem.style.display = 'block';
    };

    /**
     * Set elem display to hide
     */
    static hide(elem) {
        elem.style.display = 'none';
    };

    /**
     * Path a segment of the window.location.pathname
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
     * 
     * Sends a Error to an endpont (for logging)
     */
    static async asyncPostError(endpoint, error) {
        const error_data = new FormData();
        error_data.set('error', error);
        return Pebble.asyncPost(endpoint, error_data);
    }

    /**
     * Remove flash messages .flash-remove
     */
    static removeFlashMessages() {
        let elems = document.querySelectorAll('.flash-remove')
        elems.forEach(function (elem) {
            elem.remove();
        })
    }

    /**
     * Redirect to URL
     */
    static redirect(url) {
        // Add to window history
        // window.location.assign(url)
        window.location.replace(url)
    }


    /**
     * @param {*} settings 
     */
    static addPostEventListener(settings) {

        const route = settings.route;
        const eventListenerElem = document.querySelector(settings.eventElem) || document.querySelector('#click');
        const loaderElem = document.querySelector(settings.loaderElem) || document.querySelector('.loadingspinner');
        const formElem = document.querySelector(settings.formElem) || document.querySelector('#form');

        const onSuccessCallbackDefault = function (response) {
            if (response.error === false) {
                if (response.redirect) {
                    Pebble.redirect(response.redirect)
                } else {
                    Pebble.setFlashMessage(response.message, 'success');
                }
            } else {
                Pebble.setFlashMessage(response.message, 'error');
            }
        };

        const onSuccessCallback = settings.onSuccessCallback || onSuccessCallbackDefault 
            
        eventListenerElem.addEventListener("click", async function(e) {

            e.preventDefault();
            loaderElem.classList.toggle('hidden');
    
            try {
                const data = new FormData(formElem);
                const res = await Pebble.asyncPost(route, data);
                onSuccessCallback(res);

            } catch (e) {
                await Pebble.asyncPostError('/error/log', e.stack);
            }
    
            loaderElem.classList.toggle('hidden');
        });
    }
}

export { Pebble }