import './main.css';
import Alpine from 'alpinejs';
import './js/ringGallery';
import './js/pageTransition';

window.Alpine = window.Alpine || Alpine;

document.addEventListener('DOMContentLoaded', () => {
  if (!window.__alpineStarted) {
    window.__alpineStarted = true;
    window.Alpine.start();
  }
});