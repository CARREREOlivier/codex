document.addEventListener('DOMContentLoaded', function () {
    const burgerButton = document.getElementById('hamburger-btn');
    const closeButton = document.getElementById('close-menu');
    const mobileMenu = document.getElementById('mobile-menu');

    if (burgerButton) {
        burgerButton.addEventListener('click', () => {
            mobileMenu.classList.remove('translate-x-full');
            mobileMenu.classList.add('translate-x-0');
        });
    }

    if (closeButton) {
        closeButton.addEventListener('click', () => {
            mobileMenu.classList.remove('translate-x-0');
            mobileMenu.classList.add('translate-x-full');
        });
    }
});
