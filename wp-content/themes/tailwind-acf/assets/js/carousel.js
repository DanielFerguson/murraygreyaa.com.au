(() => {
	const ROOT_SELECTOR = '[data-carousel-root]';

	class TailwindCarousel {
		constructor(root) {
			this.root = root;
			this.viewport = root.querySelector('[data-carousel-viewport]');
			this.track = root.querySelector('[data-carousel-track]');
			this.prevButtons = Array.from(root.querySelectorAll('[data-carousel-prev]'));
			this.nextButtons = Array.from(root.querySelectorAll('[data-carousel-next]'));
			this.controls = root.querySelector('[data-carousel-controls]');
			this.dotsContainer = root.querySelector('[data-carousel-dots-container]');
			this.dots = this.dotsContainer ? Array.from(this.dotsContainer.querySelectorAll('[data-carousel-dot]')) : [];
			this.autoplayEnabled = root.dataset.carouselAutoplay !== 'false';
			this.interval = parseInt(root.dataset.carouselInterval || '3000', 10);
			this.sourceSlides = Array.from(this.track.children).map((slide) => slide.cloneNode(true));
			this.originalCount = this.sourceSlides.length;
			this.visible = Math.min(this.getVisibleCount(), this.originalCount || 1);
			this.current = 0;
			this.slideWidth = 0;
			this.snapDistance = 0;
			this.autoTimer = null;
			this.isPointerInside = false;
			this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

			this.handleResize = this.handleResize.bind(this);
			this.handleTransitionEnd = this.handleTransitionEnd.bind(this);
			this.handlePointerEnter = this.handlePointerEnter.bind(this);
			this.handlePointerLeave = this.handlePointerLeave.bind(this);
			this.handleMotionChange = this.handleMotionChange.bind(this);
			this.handleDotClick = this.handleDotClick.bind(this);

			if (!this.viewport || !this.track || this.originalCount === 0) {
				return;
			}

			if (this.prefersReducedMotion.addEventListener) {
				this.prefersReducedMotion.addEventListener('change', this.handleMotionChange);
			} else if (this.prefersReducedMotion.addListener) {
				this.prefersReducedMotion.addListener(this.handleMotionChange);
			}

			if (this.originalCount <= this.visible) {
				this.renderStatic();
				return;
			}

			this.setup();
			this.bindEvents();
			this.startAuto();
		}

		getVisibleCount() {
			if (window.innerWidth >= 1024) {
				return 3;
			}
			if (window.innerWidth >= 768) {
				return 2;
			}
			return 1;
		}

		renderStatic() {
			this.stopAuto();
			this.track.innerHTML = '';
			const styles = window.getComputedStyle(this.track);
			const gap = parseFloat(styles.columnGap || styles.rowGap || styles.gap || '0') || 0;
			this.snapDistance = this.visible > 0 ? (this.viewport.clientWidth - gap * (this.visible - 1)) / this.visible + gap : 0;
			this.sourceSlides.forEach((slide) => {
				const node = slide.cloneNode(true);
				node.style.flex = `0 0 ${100 / this.visible}%`;
				node.style.maxWidth = `${100 / this.visible}%`;
				this.track.appendChild(node);
			});
			this.slides = Array.from(this.track.children);
			this.current = 0;
			this.track.style.transitionDuration = '0ms';
			this.track.style.transform = 'translateX(0)';
			this.track.getBoundingClientRect();
			this.track.style.transitionDuration = '';
			this.toggleControls(false);
			this.updateIndicators();
			this.root.classList.add('carousel-static');
		}

		setup() {
			this.stopAuto();
			this.root.classList.remove('carousel-static');
			this.toggleControls(true);

			this.visible = Math.min(this.getVisibleCount(), this.originalCount);
			const prefix = this.sourceSlides.slice(-this.visible).map((slide) => slide.cloneNode(true));
			const originals = this.sourceSlides.map((slide) => slide.cloneNode(true));
			const suffix = this.sourceSlides.slice(0, this.visible).map((slide) => slide.cloneNode(true));

			this.track.innerHTML = '';
			[...prefix, ...originals, ...suffix].forEach((slide) => {
				slide.dataset.carouselSlide = 'true';
				this.track.appendChild(slide);
			});

			this.slides = Array.from(this.track.children);
			this.current = this.visible;
			this.applyWidths();
			this.jumpTo(this.current, false);
		}

		applyWidths() {
			const styles = window.getComputedStyle(this.track);
			const gap = parseFloat(styles.columnGap || styles.rowGap || styles.gap || '0') || 0;
			const viewportWidth = this.viewport.clientWidth;
			this.slideWidth = (viewportWidth - gap * (this.visible - 1)) / this.visible;
			this.snapDistance = this.slideWidth + gap;

			this.slides.forEach((slide) => {
				slide.style.flex = `0 0 ${this.slideWidth}px`;
				slide.style.maxWidth = `${this.slideWidth}px`;
			});

			this.jumpTo(this.current, false);
			this.updateIndicators();
		}

		jumpTo(index, animate = true) {
			if (!this.slides || !this.slides.length) {
				return;
			}

			const duration = animate ? '600ms' : '0ms';
			this.track.style.transitionDuration = duration;
			this.track.style.transform = `translateX(${-index * this.snapDistance}px)`;

			if (!animate) {
				this.track.getBoundingClientRect();
				this.track.style.transitionDuration = '';
			}
		}

		goTo(index, userAction = false) {
			this.current = index;
			this.jumpTo(this.current, true);
			this.updateIndicators();
			if (userAction) {
				this.restartAuto();
			}
		}

		next(userAction = false) {
			this.goTo(this.current + 1, userAction);
		}

		prev(userAction = false) {
			this.goTo(this.current - 1, userAction);
		}

		handleTransitionEnd() {
			const maxIndex = this.visible + this.originalCount;
			if (this.current >= maxIndex) {
				this.current = this.visible;
				this.jumpTo(this.current, false);
			} else if (this.current < this.visible) {
				this.current = this.originalCount;
				this.jumpTo(this.current, false);
			}
			this.updateIndicators();
		}

		startAuto() {
			if (!this.autoplayEnabled || this.prefersReducedMotion.matches || this.originalCount <= this.visible) {
				return;
			}
			this.stopAuto();
			this.autoTimer = window.setInterval(() => {
				if (!this.isPointerInside) {
					this.next(false);
				}
			}, this.interval);
		}

		stopAuto() {
			if (this.autoTimer) {
				window.clearInterval(this.autoTimer);
				this.autoTimer = null;
			}
		}

		restartAuto() {
			this.stopAuto();
			this.startAuto();
		}

		handlePointerEnter(event) {
			if (event.type === 'focusin' && !this.root.contains(event.target)) {
				return;
			}
			this.isPointerInside = true;
			this.stopAuto();
		}

		handlePointerLeave(event) {
			if (event.type === 'focusout' && this.root.contains(event.relatedTarget)) {
				return;
			}
			this.isPointerInside = false;
			this.startAuto();
		}

		handleResize() {
			const newVisible = Math.min(this.getVisibleCount(), this.originalCount);
			if (this.originalCount <= newVisible) {
				this.visible = newVisible;
				this.renderStatic();
				return;
			}

			if (newVisible !== this.visible) {
				this.visible = newVisible;
				this.setup();
			} else {
				this.applyWidths();
			}

			this.startAuto();
			this.updateIndicators();
		}

		handleMotionChange() {
			if (this.prefersReducedMotion.matches) {
				this.stopAuto();
			} else {
				this.startAuto();
			}
		}

		bindEvents() {
			this.nextButtons.forEach((btn) => btn.addEventListener('click', () => this.next(true)));
			this.prevButtons.forEach((btn) => btn.addEventListener('click', () => this.prev(true)));
			this.track.addEventListener('transitionend', this.handleTransitionEnd);
			window.addEventListener('resize', this.handleResize);
			this.root.addEventListener('mouseenter', this.handlePointerEnter);
			this.root.addEventListener('mouseleave', this.handlePointerLeave);
			this.root.addEventListener('focusin', this.handlePointerEnter);
			this.root.addEventListener('focusout', this.handlePointerLeave);
			this.dots.forEach((dot) => dot.addEventListener('click', this.handleDotClick));
		}

		handleDotClick(event) {
			const target = event.currentTarget;
			const index = parseInt(target.dataset.carouselDot || '0', 10);
			if (Number.isNaN(index)) {
				return;
			}
			const destination = this.originalCount > this.visible ? this.visible + index : index;
			this.goTo(destination, true);
		}

		normalizeIndex(index = this.current) {
			if (this.originalCount <= this.visible) {
				return 0;
			}
			const raw = index - this.visible;
			const mod = ((raw % this.originalCount) + this.originalCount) % this.originalCount;
			return mod;
		}

		updateIndicators() {
			if (!this.dots.length) {
				return;
			}
			const activeIndex = this.normalizeIndex();
			this.dots.forEach((dot, index) => {
				const isActive = index === activeIndex;
				dot.classList.toggle('bg-brand', isActive);
				dot.classList.toggle('border-brand', isActive);
				dot.classList.toggle('shadow', isActive);
				dot.setAttribute('aria-current', isActive ? 'true' : 'false');
			});
		}

		toggleControls(show) {
			this.prevButtons.forEach((btn) => {
				if (show) {
					btn.removeAttribute('hidden');
					btn.removeAttribute('disabled');
				} else {
					btn.setAttribute('hidden', 'hidden');
					btn.setAttribute('disabled', 'disabled');
				}
			});
			this.nextButtons.forEach((btn) => {
				if (show) {
					btn.removeAttribute('hidden');
					btn.removeAttribute('disabled');
				} else {
					btn.setAttribute('hidden', 'hidden');
					btn.setAttribute('disabled', 'disabled');
				}
			});
			if (this.controls) {
				if (show) {
					this.controls.removeAttribute('hidden');
				} else {
					this.controls.setAttribute('hidden', 'hidden');
				}
			}
			if (this.dotsContainer) {
				if (show) {
					this.dotsContainer.removeAttribute('hidden');
				} else {
					this.dotsContainer.setAttribute('hidden', 'hidden');
				}
			}
		}
	}

	function initCarousel(root) {
		if (root.dataset.carouselInitialized === 'true') {
			return;
		}
		root.dataset.carouselInitialized = 'true';
		new TailwindCarousel(root);
	}

	function scan() {
		document.querySelectorAll(ROOT_SELECTOR).forEach(initCarousel);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', scan);
	} else {
		scan();
	}

	const observer = new MutationObserver((mutations) => {
		for (const mutation of mutations) {
			mutation.addedNodes.forEach((node) => {
				if (!(node instanceof HTMLElement)) {
					return;
				}
				if (node.matches?.(ROOT_SELECTOR)) {
					initCarousel(node);
				} else {
					node.querySelectorAll?.(ROOT_SELECTOR).forEach(initCarousel);
				}
			});
		}
	});

	observer.observe(document.documentElement, { childList: true, subtree: true });
})();
