window.acceptCookies = function () {
    localStorage.setItem('cookiesAccepted', 'true');
    document.getElementById('cookieBanner').classList.add('hidden');
};

document.addEventListener('DOMContentLoaded', function() {
    if (!localStorage.getItem('cookiesAccepted')) {
        document.getElementById('cookieBanner').classList.remove('hidden');
    }
});