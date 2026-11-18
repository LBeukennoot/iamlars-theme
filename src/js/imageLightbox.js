// src/js/imageLightbox.js
window.Alpine.data("imageLightbox", () => {
  return {
    isOpen: false,
    src: "",
    alt: "",
    caption: "",

    init() {
      // Event delegation so all .wp-block-image blocks on the page work automatically
      document.addEventListener("click", (e) => {
        const figure = e.target.closest(".wp-block-image");
        if (!figure) return;

        // Ignore if the image is wrapped in a custom link (<a href="...">)
        if (e.target.closest("a")) return;

        const img = figure.querySelector("img");
        if (!img) return;

        e.preventDefault();
        this.open(img, figure);
      });
    },

    open(img, figure) {
      // Prefer full-size data attribute if present, otherwise currentSrc / src
      this.src = img.dataset.fullUrl || img.currentSrc || img.src;
      this.alt = img.alt || "";
      this.caption = figure.querySelector("figcaption")?.innerHTML.trim() || "";
      this.isOpen = true;
      document.documentElement.classList.add("overflow-hidden");
    },

    close() {
      this.isOpen = false;
      document.documentElement.classList.remove("overflow-hidden");
    },
  };
});
