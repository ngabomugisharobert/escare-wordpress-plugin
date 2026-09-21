/**
 * Mobile primary menu.
 */
(function () {
	var toggle = document.querySelector(".escare-menu-toggle");
	var panel = document.getElementById("escare-primary-menu");

	if (!toggle || !panel) {
		return;
	}

	toggle.addEventListener("click", function () {
		var open = toggle.getAttribute("aria-expanded") === "true";
		toggle.setAttribute("aria-expanded", open ? "false" : "true");
		document.body.classList.toggle("escare-nav-open", !open);
	});

	panel.querySelectorAll("a").forEach(function (link) {
		link.addEventListener("click", function () {
			toggle.setAttribute("aria-expanded", "false");
			document.body.classList.remove("escare-nav-open");
		});
	});
})();
