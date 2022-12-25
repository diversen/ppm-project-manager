import {Pebble} from '/js/pebble.js';

const follow = document.querySelectorAll('.follow');
follow.forEach(function(el) {
    el.addEventListener('click', async function(e) {
        e.preventDefault();

        const ticker_id = el.getAttribute('data-ticker-id');
        const action = el.getAttribute('data-action');

        const data = new FormData()
        data.append('ticker_id', ticker_id);

        let url = '/follow';
        if (action === 'unfollow') {
            url = '/unfollow';
        }

        const res = await Pebble.asyncPost(url, data);
        if (res.error === false) {

            const new_action = (action === 'follow') ? 'unfollow' : 'follow';
            console.log(new_action)

            el.setAttribute('data-action', new_action);
            el.classList.toggle('green');
            Pebble.setFlashMessage(res.message, 'success', 3000);
        } else {
            Pebble.setFlashMessage(res.message, 'error', 3000);
        }
    });
});


let Follow = {};
export {Follow};