document.addEventListener('DOMContentLoaded', function() {
    const video = document.querySelector('video');
    if (video) {
        video.playbackRate = 0.5; // Ralentir la vidéo

        video.addEventListener('play', () => {
            video.classList.add('fade-in');
            setTimeout(() => video.classList.remove('fade-in'), 2000);
        });

        video.addEventListener('ended', () => {
            video.classList.add('fade-out');
        });

        // Pour une boucle propre avec fade
        video.addEventListener('timeupdate', () => {
            if (video.duration - video.currentTime < 2) {
                video.classList.add('fade-out');
            }
        });

        video.addEventListener('seeked', () => {
            video.classList.remove('fade-out');
            video.classList.add('fade-in');
            setTimeout(() => video.classList.remove('fade-in'), 2000);
        });
    }
});