(function () {
	'use strict';

	var status = document.getElementById('esc_status');
	if (!status) {
		return;
	}

	status.addEventListener('change', function () {
		status.setAttribute('data-changed', '1');
	});
})();
