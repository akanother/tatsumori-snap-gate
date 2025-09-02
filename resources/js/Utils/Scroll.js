const scrollToTop = () => {
    const header = document.querySelector('header.app-header');
    const headerHeight = header ? header.offsetHeight : 0;
    window.scrollTo({ top: headerHeight, behavior: 'smooth' });
}

export {
    scrollToTop,
}
