function addEventListenerAll(elems, event, cb) {
    const aryElems = Array.from(elems)
    aryElems.forEach(elem => {
        elem.addEventListener(event, cb)
    })
}

export {addEventListenerAll}