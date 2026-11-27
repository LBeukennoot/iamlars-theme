import Alpine from "alpinejs";
import "./main.css";

window.Alpine = window.Alpine || Alpine;

import "./js/ringGallery";
import "./js/pageTransition";
import "./js/imageLightbox";

document.addEventListener("DOMContentLoaded", () => {
  if (!window.__alpineStarted) {
    window.__alpineStarted = true;
    window.Alpine.start();
  }
});
