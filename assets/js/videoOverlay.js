document.addEventListener('DOMContentLoaded', function() {
    const video = document.querySelector('video');
    const overlay = document.getElementById('video-fade-overlay');

    if (video && overlay) {
        video.playbackRate = 0.5; // Ralentir la lecture

        const fadeOut = () => overlay.style.opacity = 1;
        const fadeIn = () => overlay.style.opacity = 0;

        video.addEventListener('play', fadeIn);

        video.addEventListener('timeupdate', () => {
            if (video.duration - video.currentTime < 2) {
                fadeOut();
            }
        });

        video.addEventListener('seeked', fadeIn);
    }
});