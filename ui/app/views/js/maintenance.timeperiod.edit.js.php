<?php declare(strict_types = 0);
/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/
?>


window.maintenance_timeperiod_edit = new class {

	init() {
		this.overlay = overlays_stack.getById('maintenance-timeperiod-edit');
		this.dialogue = this.overlay.$dialogue[0];
		this.form = this.overlay.$dialogue.$body[0].querySelector('form');

		// Update form field state according to the form data.

		document.querySelectorAll('[name="timeperiod_type"], [name="month_date_type"]').forEach((element) => {
			element.addEventListener('change', () => this._update());
		});

		this._update();

		document.getElementById('maintenance-timeperiod-form').style.display = '';
		this.form.querySelector('[name="timeperiod_type"]').focus();
	}

	_update() {
		const timeperiod_type_value = this.form.querySelector('[name="timeperiod_type"]').value;
		const month_date_type_value = this.form.querySelector('[name="month_date_type"]:checked').value;

		this.form.querySelectorAll('.js-every-day').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_DAILY ?>;
		});

		this.form.querySelectorAll('.js-every-week').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_WEEKLY ?>;
		});

		this.form.querySelectorAll('.js-weekly-days').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_WEEKLY ?>;
		});

		this.form.querySelectorAll('.js-months').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_MONTHLY ?>;
		});

		this.form.querySelectorAll('.js-month-date-type').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_MONTHLY ?>;
		});

		this.form.querySelectorAll('.js-every-dow').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_MONTHLY ?>
				|| month_date_type_value != 1;
		});

		this.form.querySelectorAll('.js-monthly-days').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_MONTHLY ?>
				|| month_date_type_value != 1;
		});

		this.form.querySelectorAll('.js-day').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_MONTHLY ?>
				|| month_date_type_value != 0;
		});

		this.form.querySelectorAll('.js-start-date').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_ONETIME ?>;
		});

		this.form.querySelectorAll('.js-hour-minute').forEach((element) => {
			element.hidden = timeperiod_type_value != <?= TIMEPERIOD_TYPE_DAILY ?>
				&& timeperiod_type_value != <?= TIMEPERIOD_TYPE_WEEKLY ?>
				&& timeperiod_type_value != <?= TIMEPERIOD_TYPE_MONTHLY ?>;
		});
	}

	submit() {
		const fields = getFormFields(this.form);

		const curl = new Curl('zabbix.php', false);
		curl.setArgument('action', 'maintenance.timeperiod.check');

		this._post(curl.getUrl(), fields, (response) => {
			overlayDialogueDestroy(this.overlay.dialogueid);

			this.dialogue.dispatchEvent(new CustomEvent('dialogue.submit', {detail: response.body}));
		});
	}

	_post(url, data, success_callback) {
		this.overlay.setLoading();

		fetch(url, {
			method: 'POST',
			headers: {'Content-Type': 'application/json'},
			body: JSON.stringify(data)
		})
			.then((response) => response.json())
			.then((response) => {
				if ('error' in response) {
					throw {error: response.error};
				}

				return response;
			})
			.then(success_callback)
			.catch((exception) => {
				for (const element of this.form.parentNode.children) {
					if (element.matches('.msg-good, .msg-bad, .msg-warning')) {
						element.parentNode.removeChild(element);
					}
				}

				let title, messages;

				if (typeof exception === 'object' && 'error' in exception) {
					title = exception.error.title;
					messages = exception.error.messages;
				}
				else {
					messages = [<?= json_encode(_('Unexpected server error.')) ?>];
				}

				const message_box = makeMessageBox('bad', messages, title)[0];

				this.form.parentNode.insertBefore(message_box, this.form);
			})
			.finally(() => {
				this.overlay.unsetLoading();
			});
	}
};
