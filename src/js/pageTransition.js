window.Alpine.store('page', {
  isLoading: false,
  scroll: 0,
  maxScroll: 0,
  progress: 0, // 0 to 1 (useful for scroll progress bars)
  isScrollingDown: false,
  lastScroll: 0,

  init() {
    this.updateScrollMetrics();
    window.addEventListener('resize', () => this.updateScrollMetrics());
  },

  updateScrollMetrics() {
    this.maxScroll = Math.max(document.documentElement.scrollHeight - window.innerHeight, 1);
  },

  handleScroll() {
    const current = window.scrollY || window.pageYOffset || 0;

    this.isScrollingDown = current > this.lastScroll;
    this.lastScroll = current;
    this.scroll = current;
    this.progress = Math.min(Math.max(current / this.maxScroll, 0), 1);
  },

  // Generic navigation function you can call from anywhere
  navigateTo(url, delay = 350) {
    this.isLoading = true;

    setTimeout(() => {
      window.location.href = url;
    }, delay);
  }
});