// function that adds the class active links found in the DOM
function setActiveClass () {

    const appMenu = document.querySelectorAll('.app-menu a')
    const actionLinks = document.querySelectorAll('.action-links a')

    // Get current url
    const pathName = window.location.pathname

    appMenu.forEach( (elem) => {
        if (elem.dataset.path === pathName) {
            elem.classList.add('active')
        }
    })

    actionLinks.forEach( (elem) => {
        
        if (elem.dataset.path === pathName) {
            elem.classList.add('active')
        }
    })
}

export {setActiveClass}
