import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-audio-action][data-audio-index]');
    if (!button) {
        return;
    }

    const audio = document.getElementById(`audio-${button.dataset.audioIndex}`);
    if (!audio) {
        return;
    }

    switch (button.dataset.audioAction) {
        case 'play':
            audio.play();
            break;
        case 'pause':
            audio.pause();
            break;
        case 'stop':
            audio.pause();
            audio.currentTime = 0;
            break;
    }
});
