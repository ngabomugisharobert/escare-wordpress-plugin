(function () {
	'use strict';

	var i18n = window.escPortal || {};
	var showLabel = i18n.showPassword || 'Show password';
	var hideLabel = i18n.hidePassword || 'Hide password';

	function eyeIcon(hidden) {
		if (hidden) {
			return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
		}
		return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
	}

	function enhancePasswordField(input) {
		if (!input || input.dataset.escToggle === '1' || input.closest('.esc-password-wrap')) {
			return;
		}

		input.dataset.escToggle = '1';

		var wrap = document.createElement('div');
		wrap.className = 'esc-password-wrap';

		var parent = input.parentNode;
		parent.insertBefore(wrap, input);
		wrap.appendChild(input);

		var button = document.createElement('button');
		button.type = 'button';
		button.className = 'esc-password-toggle';
		button.setAttribute('aria-label', showLabel);
		button.setAttribute('aria-pressed', 'false');
		button.innerHTML = eyeIcon(true);

		button.addEventListener('click', function () {
			var showing = input.type === 'text';
			input.type = showing ? 'password' : 'text';
			button.setAttribute('aria-pressed', showing ? 'false' : 'true');
			button.setAttribute('aria-label', showing ? showLabel : hideLabel);
			button.innerHTML = eyeIcon(showing);
		});

		wrap.appendChild(button);
	}

	function initPasswordToggles(root) {
		var scope = root || document;
		var inputs = scope.querySelectorAll('.esc-portal-wrap input[type="password"], .esc-dash-wrap input[type="password"]');
		for (var i = 0; i < inputs.length; i++) {
			enhancePasswordField(inputs[i]);
		}
	}

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
			var options = document.querySelectorAll('.esc-role-option');
			for (var o = 0; o < options.length; o++) {
				options[o].classList.toggle('is-selected', options[o].contains(input) && input.checked);
			}
			if (company) {
				var isEmployer = input.value === 'employer' && input.checked;
				company.hidden = !isEmployer;
				if (companyInput) {
					companyInput.required = isEmployer;
					if (!isEmployer) {
						companyInput.value = '';
					}
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

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initPasswordToggles();
			initDataTables();
		});
	} else {
		initPasswordToggles();
		initDataTables();
	}

	function initDataTables() {
		var toolbars = document.querySelectorAll('[data-esc-table-toolbar]');
		for (var i = 0; i < toolbars.length; i++) {
			bindDataTable(toolbars[i]);
		}
	}

	function bindDataTable(toolbar) {
		var tableId = toolbar.getAttribute('data-esc-table-toolbar');
		var table = document.getElementById(tableId);
		if (!table) {
			return;
		}

		var tbody = table.tBodies[0];
		if (!tbody) {
			return;
		}

		var search = toolbar.querySelector('.esc-data-search-input');
		var filters = toolbar.querySelectorAll('[data-esc-filter]');
		var countEl = toolbar.querySelector('.esc-data-count');
		var sortButtons = table.querySelectorAll('.esc-sort');
		var sortKey = '';
		var sortDir = 'asc';

		function rows() {
			return Array.prototype.slice.call(tbody.querySelectorAll('tr')).filter(function (row) {
				return !row.classList.contains('esc-data-empty');
			});
		}

		function applyFilter() {
			var q = search ? String(search.value || '').toLowerCase().trim() : '';
			var filterMap = {};
			for (var f = 0; f < filters.length; f++) {
				var key = filters[f].getAttribute('data-esc-filter');
				filterMap[key] = String(filters[f].value || '').toLowerCase();
			}

			var visible = 0;
			var all = rows();
			for (var r = 0; r < all.length; r++) {
				var row = all[r];
				var hay = String(row.getAttribute('data-esc-search') || '').toLowerCase();
				var show = !q || hay.indexOf(q) !== -1;

				if (show) {
					for (var keyName in filterMap) {
						if (!Object.prototype.hasOwnProperty.call(filterMap, keyName)) {
							continue;
						}
						var wanted = filterMap[keyName];
						if (!wanted) {
							continue;
						}
						var actual = String(row.getAttribute('data-esc-' + keyName) || '').toLowerCase();
						if (actual !== wanted) {
							show = false;
							break;
						}
					}
				}

				row.classList.toggle('is-hidden', !show);
				if (show) {
					visible += 1;
				}
			}

			if (countEl) {
				countEl.textContent = visible + ' of ' + all.length;
			}
		}

		function applySort(key, type, button) {
			if (sortKey === key) {
				sortDir = sortDir === 'asc' ? 'desc' : 'asc';
			} else {
				sortKey = key;
				sortDir = 'asc';
			}

			for (var s = 0; s < sortButtons.length; s++) {
				sortButtons[s].classList.remove('is-asc', 'is-desc');
			}
			button.classList.add(sortDir === 'asc' ? 'is-asc' : 'is-desc');

			var list = rows();
			list.sort(function (a, b) {
				var av = a.getAttribute('data-esc-' + key) || '';
				var bv = b.getAttribute('data-esc-' + key) || '';

				if (type === 'date' || type === 'number') {
					av = parseFloat(av) || 0;
					bv = parseFloat(bv) || 0;
					return sortDir === 'asc' ? av - bv : bv - av;
				}

				av = String(av).toLowerCase();
				bv = String(bv).toLowerCase();
				if (av < bv) {
					return sortDir === 'asc' ? -1 : 1;
				}
				if (av > bv) {
					return sortDir === 'asc' ? 1 : -1;
				}
				return 0;
			});

			for (var i = 0; i < list.length; i++) {
				tbody.appendChild(list[i]);
			}
		}

		if (search) {
			search.addEventListener('input', applyFilter);
		}
		for (var fi = 0; fi < filters.length; fi++) {
			filters[fi].addEventListener('change', applyFilter);
		}
		for (var si = 0; si < sortButtons.length; si++) {
			(function (btn) {
				btn.addEventListener('click', function () {
					applySort(btn.getAttribute('data-esc-sort'), btn.getAttribute('data-esc-sort-type') || 'text', btn);
				});
			})(sortButtons[si]);
		}

		applyFilter();
	}
})();
