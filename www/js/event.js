function addMultipleEventListener(elem, events, cb) {
    events.forEach(event => {
        elem.addEventListener(event, cb)
    });
}

function addEventListenerAll(elems, event, cb) {
    const aryElems = Array.from(elems)
    aryElems.forEach(elem => {
        elem.addEventListener(event, cb)
    })
}

export {addMultipleEventListener, addEventListenerAll}