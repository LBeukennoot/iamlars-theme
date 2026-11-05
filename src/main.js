import './main.css';
import Alpine from 'alpinejs';

Alpine.store('page', {
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

Alpine.data('ringGallery', (posts = []) => ({
  posts,
  rotation: 0,
  isDragging: false,
  hasDragged: false,
  startX: 0,
  startRotation: 0,
  velocity: 0,
  lastX: 0,
  lastTime: 0,
  springForce: 0.1,
  dampingFactor: 0.75,

  isExiting: false,

  // Geometry parameters:
  // - radius: Distance from the cards down to the invisible pivot point
  // - angleStep: Degrees of separation between adjacent cards along the arc
  radius: 1600,
  angleStep: 15,

  get count() {
    return this.posts.length;
  },

  get maxRotation() {
    // Pulling right to bring the first card (index 0) to center
    return ((this.count - 1) / 2) * this.angleStep;
  },

  get minRotation() {
    // Pulling left to bring the last card (index count - 1) to center
    return -this.maxRotation;
  },

  get activeIndex() {
    // Rotation 0 centers the middle card: index = (count - 1) / 2
    const centerOffset = (this.count - 1) / 2;
    const rawIndex = centerOffset - Math.round(this.rotation / this.angleStep);

    // Clamp to ensure it never goes out of array bounds
    return Math.max(0, Math.min(this.count - 1, rawIndex));
  },

  get activePost() {
    return this.posts[this.activeIndex] || {};
  },

  clamp(val, min, max) {
    return Math.max(min, Math.min(max, val));
  },

  onPointerDown(e) {
    this.isDragging = true;
    this.hasDragged = false;
    this.startX = e.clientX;
    this.lastX = e.clientX;
    this.startRotation = this.rotation;
    this.lastTime = performance.now();
    this.velocity = 0;
  },

  onPointerMove(e) {
    if (!this.isDragging || this.isExiting) return;

    const now = performance.now();
    const dt = Math.max(now - this.lastTime, 1);
    const dx = e.clientX - this.lastX;

    this.velocity = (dx / dt) * 0.4;
    this.lastX = e.clientX;
    this.lastTime = now;

    const deltaX = e.clientX - this.startX;
    // Rotate 1 degree per ~18px dragged
    this.rotation = this.startRotation + deltaX * 0.055;

    if (Math.abs(deltaX) > 5) {
      this.hasDragged = true;
    }
  },

  onPointerUp() {
    if (!this.isDragging) return;
    this.isDragging = false;
    this.applyInertia();
  },

  handleCardClick(index) {
    if (this.hasDragged) return;
    this.rotateToIndex(index);
  },

  applyInertia() {
    const decay = () => {
      if (this.isDragging) return;

      // Check if we are outside the valid boundary
      const isOverMax = this.rotation > this.maxRotation;
      const isUnderMin = this.rotation < this.minRotation;
      const isOutOfBounds = isOverMax || isUnderMin;

      // Case 1: Out of bounds (Rubber-band snap back)
      if (isOutOfBounds) {
        const boundaryTarget = isOverMax ? this.maxRotation : this.minRotation;
        const distance = boundaryTarget - this.rotation;

        // Spring pull back toward the boundary edge
        const springForce = distance * this.springForce;
        this.velocity += springForce;
        this.velocity *= this.dampingFactor; // Heavy damping so it doesn't bounce endlessly
        this.rotation += this.velocity;

        if (Math.abs(distance) > 0.02 || Math.abs(this.velocity) > 0.02) {
          requestAnimationFrame(decay);
        } else {
          this.rotation = boundaryTarget;
          this.velocity = 0;
        }
        return;
      }

      // Case 2: In bounds & high velocity (Natural coasting)
      if (Math.abs(this.velocity) > 0.4) {
        this.rotation += this.velocity;
        this.velocity *= 0.92; // Standard air friction
        requestAnimationFrame(decay);
        return;
      }

      // Case 3: In bounds & low velocity (Snap to nearest valid card)
      const rawTarget = Math.round(this.rotation / this.angleStep) * this.angleStep;
      const target = this.clamp(rawTarget, this.minRotation, this.maxRotation);
      const distance = target - this.rotation;

      const springForce = distance * this.springForce;
      this.velocity += springForce;
      this.velocity *= this.dampingFactor;
      this.rotation += this.velocity;

      if (Math.abs(distance) > 0.01 || Math.abs(this.velocity) > 0.01) {
        requestAnimationFrame(decay);
      } else {
        this.rotation = target;
        this.velocity = 0;
      }
    };

    requestAnimationFrame(decay);
  },

  rotateToIndex(index) {
    // To center card `index`, rotate by the negative of its base offset
    const target = -((index - (this.count - 1) / 2) * this.angleStep);

    // Set the target, cancel any leftover flick velocity, and trigger inertia
    this.velocity = 0;

    // Animate toward target using spring decay
    const animateToTarget = () => {
      if (this.isDragging) return;

      const distance = target - this.rotation;
      const springForce = distance * this.springForce;

      this.velocity += springForce;
      this.velocity *= this.dampingFactor; // damping factor
      this.rotation += this.velocity;

      if (Math.abs(distance) > 0.01 || Math.abs(this.velocity) > 0.01) {
        requestAnimationFrame(animateToTarget);
      } else {
        this.rotation = target;
        this.velocity = 0;
      }
    };

    requestAnimationFrame(animateToTarget);
  },

  navigate(url) {
    this.isExiting = true;
    window.location.href = url;
  },

  /**
   * Calculates transform for each card along the arc.
   * Cards rotate around the anchor point located 'radius' pixels down.
   */
  getCardTransform(index) {
    // Center the spread across 0 degrees
    const baseAngle = (index - (this.count - 1) / 2) * this.angleStep;
    const currentAngle = baseAngle + this.rotation;

    // Optional dynamic scale/opacity: card in center is slightly larger
    const distFromCenter = Math.abs(currentAngle);
    // const scale = Math.max(0.85, 1 - distFromCenter * 0.004);
    const opacity = Math.max(0.2, 1 - distFromCenter * 0.018);

    return {
      transformOrigin: `50% ${this.radius}px`,
      transform: `rotate(${currentAngle}deg) `,
      opacity: opacity
    };
  }
}));

window.Alpine = Alpine;
Alpine.start();
