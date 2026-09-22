(function () {
	'use strict';

	var i18n = window.escPortal || {};
	var showLabel = i18n.showPassword || 'Show password';
	var hideLabel = i18n.hidePassword || 'Hide password';
	var resumeTooBig = i18n.resumeTooBig || 'That resume is larger than the allowed file size.';
	var zeroResults = i18n.zeroResults || 'No matching rows on this page.';
	var resultCount = i18n.resultCount || '%1$s of %2$s on this page (%3$s total)';

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
			window.alert(resumeTooBig);
			input.value = '';
		}
	});

	function syncRolePicker() {
		var selected = document.querySelector('input[name="esc_role"]:checked');
		if (!selected) {
			return;
		}
		selected.dispatchEvent(new Event('change', { bubbles: true }));
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initPasswordToggles();
			initDataTables();
			initUserModal();
			initMessageModal();
			syncRolePicker();
		});
	} else {
		initPasswordToggles();
		initDataTables();
		initUserModal();
		initMessageModal();
		syncRolePicker();
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
		var headers = table.querySelectorAll('th[scope="col"]');
		var sortKey = '';
		var sortDir = 'asc';
		var emptyRow = tbody.querySelector('.esc-data-empty');
		var total = countEl ? parseInt(countEl.getAttribute('data-esc-total') || '0', 10) : 0;
		var colCount = table.querySelectorAll('thead th').length || 1;

		if (!emptyRow) {
			emptyRow = document.createElement('tr');
			emptyRow.className = 'esc-data-empty is-hidden';
			emptyRow.innerHTML = '<td colspan="' + colCount + '">' + zeroResults + '</td>';
			tbody.appendChild(emptyRow);
		}

		function rows() {
			return Array.prototype.slice.call(tbody.querySelectorAll('tr')).filter(function (row) {
				return !row.classList.contains('esc-data-empty');
			});
		}

		function announceCount(visible, pageTotal) {
			if (!countEl) {
				return;
			}

			var complete = total || pageTotal;
			countEl.textContent = resultCount
				.replace('%1$s', String(visible))
				.replace('%2$s', String(pageTotal))
				.replace('%3$s', String(complete));
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

			if (emptyRow) {
				emptyRow.classList.toggle('is-hidden', visible > 0 || all.length === 0);
				if (visible === 0 && all.length) {
					emptyRow.querySelector('td').textContent = zeroResults;
				}
			}

			announceCount(visible, all.length);
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

			for (var h = 0; h < headers.length; h++) {
				headers[h].setAttribute('aria-sort', 'none');
			}
			var header = button.closest('th');
			if (header) {
				header.setAttribute('aria-sort', sortDir === 'asc' ? 'ascending' : 'descending');
			}

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
			if (emptyRow) {
				tbody.appendChild(emptyRow);
			}

			button.focus();
		}

		if (search) {
			search.addEventListener('input', applyFilter);
		}
		for (var fi = 0; fi < filters.length; fi++) {
			filters[fi].addEventListener('change', applyFilter);
		}
		for (var si = 0; si < sortButtons.length; si++) {
			(function (btn) {
				if (btn.tagName === 'A') {
					return;
				}
				btn.addEventListener('click', function () {
					applySort(btn.getAttribute('data-esc-sort'), btn.getAttribute('data-esc-sort-type') || 'text', btn);
				});
			})(sortButtons[si]);
		}

		applyFilter();
	}

	function initUserModal() {
		var dialog = document.getElementById('esc-user-modal');
		if (!dialog) {
			return;
		}

		var opener = null;

		function setValue(selector, value) {
			var nodes = dialog.querySelectorAll(selector);
			for (var i = 0; i < nodes.length; i++) {
				nodes[i].value = value || '';
			}
		}

		function fill(button) {
			setValue('[data-esc-modal-user-id]', button.getAttribute('data-user-id'));
			setValue('[data-esc-modal-delete-nonce]', button.getAttribute('data-delete-nonce'));
			setValue('[data-esc-modal-resend-nonce]', button.getAttribute('data-resend-nonce'));
			setValue('[data-esc-modal-approve-nonce]', button.getAttribute('data-approve-nonce'));
			setValue('[data-esc-modal-reject-nonce]', button.getAttribute('data-reject-nonce'));

			var nameEl = dialog.querySelector('[data-esc-modal-name]');
			var emailEl = dialog.querySelector('[data-esc-modal-email]');
			if (nameEl) {
				nameEl.textContent = button.getAttribute('data-name') || '';
			}
			if (emailEl) {
				emailEl.textContent = button.getAttribute('data-email') || '';
			}

			var role = dialog.querySelector('[data-esc-modal-role]');
			var status = dialog.querySelector('[data-esc-modal-status]');
			if (role) {
				role.value = button.getAttribute('data-role') || '';
			}
			if (status) {
				status.value = button.getAttribute('data-status') || '';
			}

			var pendingEmail = dialog.querySelector('[data-esc-modal-pending-email]');
			var pendingAdmin = dialog.querySelector('[data-esc-modal-pending-admin]');
			var del = dialog.querySelector('[data-esc-modal-delete]');
			if (pendingEmail) {
				pendingEmail.hidden = button.getAttribute('data-pending-email') !== '1';
			}
			if (pendingAdmin) {
				pendingAdmin.hidden = button.getAttribute('data-pending-admin') !== '1';
			}
			if (del) {
				del.hidden = button.getAttribute('data-can-delete') !== '1';
			}
		}

		function openModal(button) {
			opener = button;
			fill(button);
			if (typeof dialog.showModal === 'function') {
				dialog.showModal();
			} else {
				dialog.setAttribute('open', 'open');
			}
			var first = dialog.querySelector('[data-esc-modal-role]');
			if (first) {
				first.focus();
			}
		}

		function closeModal() {
			if (typeof dialog.close === 'function' && dialog.open) {
				dialog.close();
			} else {
				dialog.removeAttribute('open');
			}
			if (opener && typeof opener.focus === 'function') {
				opener.focus();
			}
		}

		document.addEventListener('click', function (event) {
			var button = event.target.closest ? event.target.closest('[data-esc-user-manage]') : null;
			if (button) {
				event.preventDefault();
				openModal(button);
				return;
			}
			if (event.target.closest && event.target.closest('[data-esc-modal-close]') && event.target.closest('.esc-modal') === dialog) {
				event.preventDefault();
				closeModal();
			}
		});

		dialog.addEventListener('click', function (event) {
			if (event.target === dialog) {
				closeModal();
			}
		});

		dialog.addEventListener('close', function () {
			if (opener && typeof opener.focus === 'function') {
				opener.focus();
			}
		});
	}

	function initMessageModal() {
		var dialog = document.getElementById('esc-contact-modal');
		if (!dialog) {
			return;
		}

		var opener = null;

		function fill(button) {
			var map = {
				'[data-esc-message-name]': button.getAttribute('data-name') || '',
				'[data-esc-message-email]': button.getAttribute('data-email') || '',
				'[data-esc-message-subject]': button.getAttribute('data-subject') || '',
				'[data-esc-message-sent]': button.getAttribute('data-sent') || '',
				'[data-esc-message-body]': button.getAttribute('data-message') || ''
			};
			Object.keys(map).forEach(function (selector) {
				var el = dialog.querySelector(selector);
				if (el) {
					el.textContent = map[selector];
				}
			});
		}

		function openModal(button) {
			opener = button;
			fill(button);
			if (typeof dialog.showModal === 'function') {
				dialog.showModal();
			} else {
				dialog.setAttribute('open', 'open');
			}
		}

		function closeModal() {
			if (typeof dialog.close === 'function' && dialog.open) {
				dialog.close();
			} else {
				dialog.removeAttribute('open');
			}
			if (opener && typeof opener.focus === 'function') {
				opener.focus();
			}
		}

		document.addEventListener('click', function (event) {
			var button = event.target.closest ? event.target.closest('[data-esc-message-view]') : null;
			if (button) {
				event.preventDefault();
				openModal(button);
				return;
			}
			if (event.target.closest && event.target.closest('[data-esc-modal-close]') && event.target.closest('.esc-modal') === dialog) {
				event.preventDefault();
				closeModal();
			}
		});

		dialog.addEventListener('click', function (event) {
			if (event.target === dialog) {
				closeModal();
			}
		});
	}
})();
