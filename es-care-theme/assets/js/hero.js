/**
 * Home hero photo slideshow.
 * Slide 1 is in the HTML. Slides 2 and 3 get src after the first 3s tick.
 */
(function () {
	var root = document.querySelector("[data-escare-hero-slides]");
	if (!root) {
		return;
	}

	var slides = root.querySelectorAll(".escare-hero-slide");
	if (slides.length < 2) {
		return;
	}

	if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
		return;
	}

	var index = 0;
	var timer = null;
	var intervalMs = 3000;
	var restLoaded = false;
	var ticking = false;

	function slideImage(slide) {
		return slide.querySelector("img");
	}

	function ensureSrc(slide) {
		var img = slideImage(slide);
		if (!img) {
			return Promise.resolve();
		}

		var pending = img.getAttribute("data-src");
		if (pending) {
			return new Promise(function (resolve) {
				function done() {
					img.removeEventListener("load", done);
					img.removeEventListener("error", done);
					resolve();
				}
				img.addEventListener("load", done);
				img.addEventListener("error", done);
				img.src = pending;
				img.removeAttribute("data-src");
			});
		}

		if (img.complete && img.naturalWidth) {
			return Promise.resolve();
		}

		return new Promise(function (resolve) {
			img.addEventListener("load", resolve, { once: true });
			img.addEventListener("error", resolve, { once: true });
		});
	}

	function loadRemaining() {
		if (restLoaded) {
			return;
		}
		restLoaded = true;
		for (var i = 0; i < slides.length; i++) {
			if (i !== index) {
				ensureSrc(slides[i]);
			}
		}
	}

	function show(next) {
		slides[index].classList.remove("is-active");
		index = next;
		slides[index].classList.add("is-active");
	}

	function tick() {
		if (ticking) {
			return;
		}
		ticking = true;
		var next = (index + 1) % slides.length;
		loadRemaining();
		ensureSrc(slides[next]).then(function () {
			show(next);
			ticking = false;
		});
	}

	function start() {
		if (timer) {
			return;
		}
		timer = window.setInterval(tick, intervalMs);
	}

	function stop() {
		if (!timer) {
			return;
		}
		window.clearInterval(timer);
		timer = null;
	}

	document.addEventListener("visibilitychange", function () {
		if (document.hidden) {
			stop();
		} else {
			start();
		}
	});

	start();
})();
