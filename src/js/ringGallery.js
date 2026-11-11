window.Alpine.data('ringGallery', (posts = []) => ({
  posts,
  rotation: 0,
  isDragging: false,
  hasDragged: false,
  startX: 0,
  startRotation: 0,
  velocity: 0,
  lastX: 0,
  lastTime: 0,
  springForce: 0.12,
  dampingFactor: 0.5,

  isExiting: false,

  radius: 1600,
  baseAngleStep: 20, // default desktop angleStep

  get angleStep() {
    if (window.innerWidth <= 640) {
      return 12;
    }
    return this.baseAngleStep;
  },

  get count() {
    return this.posts.length;
  },

  get maxRotation() {
    if (this.count <= 1) return 0;
    return ((this.count - 1) / 2) * this.angleStep;
  },

  get minRotation() {
    return -this.maxRotation;
  },

  get activeIndex() {
    if (this.count <= 1) return 0;
    const centerOffset = (this.count - 1) / 2;
    // Derive the index directly from rotation and clamp
    const rawIndex = Math.round(centerOffset - this.rotation / this.angleStep);
    return Math.max(0, Math.min(this.count - 1, rawIndex));
  },

  get activePost() {
    return this.posts[this.activeIndex] || {};
  },

  init() {
    if (this.count === 0) return;

    // Set initial centered rotation
    const initialIndex = Math.floor((this.count - 1) / 2);
    this.rotation = this.getRotationForIndex(initialIndex);

    // Re-snap cleanly if user resizes browser or rotates device
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        // Keep the currently active card centered when angleStep adjusts
        this.rotation = this.getRotationForIndex(this.activeIndex);
      }, 100);
    });
  },

  // Helper: Returns the exact rotation angle needed to center any card index
  getRotationForIndex(index) {
    if (this.count <= 1) return 0;
    return -((index - (this.count - 1) / 2) * this.angleStep);
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

      const isOverMax = this.rotation > this.maxRotation;
      const isUnderMin = this.rotation < this.minRotation;
      const isOutOfBounds = isOverMax || isUnderMin;

      // Case 1: Out of bounds (Rubber-band snap back)
      if (isOutOfBounds) {
        const boundaryTarget = isOverMax ? this.maxRotation : this.minRotation;
        const distance = boundaryTarget - this.rotation;

        const springForce = distance * this.springForce;
        this.velocity += springForce;
        this.velocity *= this.dampingFactor;
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
        this.velocity *= 0.92;
        requestAnimationFrame(decay);
        return;
      }

      // Case 3: In bounds & low velocity (Snap to nearest valid card)
      // Uses getRotationForIndex() so target rotation is aligned with even and uneven card grids
      const target = this.getRotationForIndex(this.activeIndex);
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
    const target = this.getRotationForIndex(index);
    this.velocity = 0;

    const animateToTarget = () => {
      if (this.isDragging) return;

      const distance = target - this.rotation;
      const springForce = distance * this.springForce;

      this.velocity += springForce;
      this.velocity *= this.dampingFactor;
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

  getCardTransform(index) {
    const baseAngle = (index - (this.count - 1) / 2) * this.angleStep;
    const currentAngle = baseAngle + this.rotation;

    const distFromCenter = Math.abs(currentAngle);
    const opacity = Math.max(0.2, 1 - distFromCenter * 0.018);

    return {
      transformOrigin: `50% ${this.radius}px`,
      transform: `rotate(${currentAngle}deg)`,
      opacity: opacity,
    };
  }
}));
