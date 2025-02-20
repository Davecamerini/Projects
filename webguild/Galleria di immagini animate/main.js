document.querySelector('container').addEventListener('click', function () {
    this.classList.add('is-open');
});


/* Hack for Safari and viewport units */
const calcViewportUnits = () => {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
};
calcViewportUnits();
window.addEventListener('resize', calcViewportUnits);