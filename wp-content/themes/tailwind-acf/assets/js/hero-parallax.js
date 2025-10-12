(function () {
	'use strict';

	const selector = '[data-parallax-speed]';
	const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

	let elements = [];
	let observer;
	let ticking = false;
	let listenersAttached = false;

	const clamp = (value, max) => {
		const limit = Math.abs(max);

		if (!limit) {
			return value;
		}

		return Math.max(Math.min(value, limit), -limit);
	};

	const refreshElements = () => {
		elements = Array.from(document.querySelectorAll(selector));
	};

	const updateTransforms = () => {
		if (!elements.length) {
			return;
		}

		elements = elements.filter((el) => el.isConnected);

		if (!elements.length) {
			return;
		}

		if (reduceMotionQuery.matches) {
			elements.forEach((el) => el.style.removeProperty('--parallax-y'));
			return;
		}

		const viewportMid = window.innerHeight / 2;

		elements.forEach((el) => {
			const speed = parseFloat(el.dataset.parallaxSpeed || '0.15');
			const maxDistance = parseFloat(el.dataset.parallaxMax || '36');
			const rect = el.getBoundingClientRect();
			const elementMid = rect.top + rect.height / 2;
			const translate = clamp((elementMid - viewportMid) * speed * -1, maxDistance);

			el.style.setProperty('--parallax-y', translate.toFixed(2) + 'px');
		});
	};

	const onFrame = () => {
		ticking = false;
		updateTransforms();
	};

	const requestTick = () => {
		if (!ticking) {
			window.requestAnimationFrame(onFrame);
			ticking = true;
		}
	};

	const handleMutations = (mutations) => {
		for (const mutation of mutations) {
			if (mutation.type !== 'childList') {
				continue;
			}

			for (const node of mutation.addedNodes) {
				if (!(node instanceof Element)) {
					continue;
				}

					if ((node.matches && node.matches(selector)) || (node.querySelector && node.querySelector(selector))) {
						refreshElements();
						attachListeners();
						requestTick();
						return;
					}
			}
		}
	};

	const attachListeners = () => {
		if (listenersAttached) {
			return;
		}

		window.addEventListener('scroll', requestTick, { passive: true });
		window.addEventListener('resize', requestTick);
		reduceMotionQuery.addEventListener('change', requestTick);
		listenersAttached = true;
	};

	const init = () => {
		refreshElements();

		if (!observer) {
			observer = new MutationObserver(handleMutations);
			observer.observe(document.documentElement, { childList: true, subtree: true });
		}

		if (!elements.length) {
			return;
		}

		attachListeners();
		requestTick();
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init, { once: true });
	} else {
		init();
	}
})();
