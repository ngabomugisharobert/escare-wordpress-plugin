(function () {
	'use strict';

	document.addEventListener('submit', function (event) {
		var form = event.target;
		if (!form || !form.getAttribute) {
			return;
		}

		var confirmMessage = form.getAttribute('data-esc-confirm');
		if (confirmMessage && !window.confirm(confirmMessage)) {
			event.preventDefault();
		}
	});

	document.addEventListener('change', function (event) {
		var input = event.target;
		if (input && input.name === 'esc_role') {
			var company = document.querySelector('.esc-company-field');
			var companyInput = document.getElementById('esc_company_name');
			if (company) {
				var isEmployer = input.value === 'employer' && input.checked;
				company.hidden = !isEmployer;
				if (companyInput) {
					companyInput.required = isEmployer;
				}
			}
		}

		if (!input || input.id !== 'esc_resume' || !input.files || !input.files[0]) {
			return;
		}

		var max = parseInt(input.getAttribute('data-esc-max'), 10);
		if (max && input.files[0].size > max) {
			window.alert('That resume is larger than the allowed file size.');
			input.value = '';
		}
	});
})();
