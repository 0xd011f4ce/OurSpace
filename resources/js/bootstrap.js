/* import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
 */

(function () {
    let elements = document.querySelectorAll(".expandable")

    for (let i = 0; i < elements.length; i++)
    {
        let element = elements [i]
        element.addEventListener("click", function () {
            this.classList.toggle("expanded")
        })
    }
})()

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
