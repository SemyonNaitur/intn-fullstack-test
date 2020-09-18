class UIComponent {
	ajaxURL;
	$viewContainer;
	loadingFadeInDur = 50;
	loadingFadeOutDur = 200;
	loadingItems = new Set;

	constructor($viewContainer, ajaxURL = '') {
		this.$viewContainer = $viewContainer;
		this.ajaxURL = ajaxURL;
	}

	init() { }

	find(selector) {
		return this.$viewContainer.find(selector);
	}

	loading(name, done = false) {
		const $el = this.find(`[data-load="${name}"]`);
		const $loading = $el.find('.loading');
		if (done) {
			this.loadingItems.delete(name);
			$loading.fadeOut(this.loadingFadeOutDur);
		} else {
			this.loadingItems.add(name);
			$loading.fadeIn(this.loadingFadeInDur);
		}
	}

	ajaxError(err) {
		console.error(err);
		alert(err.message);
	}

	ajaxFail() {
		alert('Server error!');
	}
}

class Salesmen extends UIComponent {
	$cityInput;
	selectedCity = '';
	$salesmenList;

	init() {
		this.initCitySearch();
		this.$salesmenList = this.find('[data-id="salesmen-list"]');
	}

	initCitySearch() {
		const $cityInput = this.find('[data-input="city"]');
		$cityInput.autocomplete({
			source: (request, response) => {
				const req = {
					method: 'get_salesman_cities',
					raw_response: 1,
					search: request.term,
					limit: 5
				};
				$.get(
					this.ajaxURL,
					req,
					res => response(res),
					'json'
				);
			},
			delay: 200,
			minLength: 1,
			select: (ev, ui) => this.onSelect(ev, ui),
		});
		this.$cityInput = $cityInput;
	}

	onSearch(ev, ui) {
		this.getSalesmanCities(ev.target.value);
	}

	getSalesmanCities(search) {
		const req = {
			method: 'get_salesman_cities',
			search,
			limit: 5
		};
		$.get(
			this.ajaxURL,
			req,
			res => this.getSalesmanCitiesSuccess(res),
			'json'
		).fail(() => this.ajaxFail())
	}

	getSalesmanCitiesSuccess(res) {
		if (res.status != 'OK') {
			this.ajaxError(res);
		} else {
			this.$cityInput.autocomplete('option', 'source', res.data);
		}
	}

	onSelect(ev, ui) {
		const val = ui.item.value;
		if (val != this.selectedCity) {
			this.selectedCity = val;
			this.getSalesmen(val);
		}
	}

	getSalesmen(city) {
		if (this.loading.salesmen) {
			return;
		}
		const loadingName = 'salesmen';
		this.loading(loadingName);
		this.$cityInput.autocomplete('disable');

		const req = { method: 'all_salesmen_from', city };
		$.get(
			this.ajaxURL,
			req,
			res => this.getSalesmenSuccess(res),
			'json'
		).fail(() => this.ajaxFail())
			.always(() => {
				this.loading(loadingName, true);
				this.$cityInput.autocomplete('enable');
			});
	}

	getSalesmenSuccess(res) {
		if (res.status != 'OK') {
			this.ajaxError(res);
		} else {
			this.drawSalesmenList(res.data);
		}
	}

	drawSalesmenList(data) {
		this.$salesmenList.empty();
		data.map(item => {
			this.$salesmenList.append(`<li class="list-group-item">${item}</li>`);
		});
	}
}

class Report extends UIComponent {
	$reportParamsForm;
	$reportRows;
	currentParams;

	init() {
		this.$reportParamsForm = this.find('[data-form="report-params"]');
		this.$reportParamsForm.submit(ev => this.onReportParamsSubmit(ev));

		this.initDateRange();
		this.$viewContainer.css('min-height', '20rem');

		this.$reportRows = this.find('[data-id="report-rows"]');
	}

	initDateRange() {
		const $fromDate = this.$reportParamsForm.find('#reportFromDate');
		const $toDate = this.$reportParamsForm.find('#reportToDate');

		$fromDate.datetimepicker({
			defaultDate: moment().subtract(10, 'y'),
			minDate: moment(0),
		});
		$toDate.datetimepicker({
			defaultDate: moment(),
		});

		$fromDate.on("change.datetimepicker", function (e) {
			$toDate.datetimepicker('minDate', e.date);
		});
		$toDate.on("change.datetimepicker", function (e) {
			$fromDate.datetimepicker('maxDate', e.date);
		});
	}

	onReportParamsSubmit(ev) {
		ev.preventDefault();
		const params = {};
		const fd = new FormData(this.$reportParamsForm[0]);
		for (let [name, value] of fd.entries()) {
			if (value && (['from_date', 'to_date'].indexOf(name) > -1)) {
				value = moment(value, 'DD/MM/YYYY').format('YYYY-MM-DD');
			}
			params[name] = value;
		}
		this.currentParams = params;
		this.getReport(params);
	}

	getReport(params) {
		if (this.loading.report) {
			return;
		}
		const loadingName = 'report';
		this.loading(loadingName);
		this.$reportParamsForm.find('[type="submit"]').prop("disabled", true);

		const req = Object.assign({ method: 'get_full_details_of_sales' }, params);
		$.get(
			this.ajaxURL,
			req,
			res => this.getReportSuccess(res),
			'json'
		).fail(() => this.ajaxFail())
			.always(() => {
				this.loading(loadingName, true);
				this.$reportParamsForm.find('[type="submit"]').prop("disabled", false);
			});
	}

	getReportSuccess(res) {
		if (res.status != 'OK') {
			this.ajaxError(res);
		} else {
			this.drawReportRows(res.data);
		}
	}

	drawReportRows(data) {
		this.$reportRows.empty();
		data.map(row => {
			const ord_date = moment(row.ord_date).format('DD/MM/YYYY');
			const tr = `
				<tr>
					<td>${row.salesman_name}</td>
					<td>${row.salesman_id}</td>
					<td>${ord_date}</td>
					<td>${row.purch_amt}</td>
					<td>${row.client_id}</td>
					<td>${row.client_name}</td>
					<td>${row.client_full_address}</td>
					<td>${row.client_gender}</td>
					<td>${row.commission_amt}</td>
				</tr>
			`;
			this.$reportRows.append(tr);
		});
	}
}

class FormCmp extends UIComponent {
	$textInput;
	$textDisplay;

	init() {
		this.initTextInput();
		this.$textDisplay = this.find('[data-id="text-display"]');
	}

	initTextInput() {
		this.$textInput = this.find('[data-input="text"]').on({
			keyup: ev => this.onTextUpdate(ev),
			change: ev => this.onTextUpdate(ev),
		});
	}

	onTextUpdate(ev) {
		this.setTextDisplayed(ev.target.value);
	}

	setTextDisplayed(text) {
		this.$textDisplay.text(text);
	}
}

function initLoadingInd($body) {
	$body.find('.loading-wrap').append(`
		<div class="loading">
			<div class="loading-overlay d-flex justify-content-center align-items-center">
				<i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i>
			</div>
		</div>
	`);
}

function loadingInd($el, done = false, animDur = 50) {
	const $loading = $el.find('.loading');
	if (done) {
		$loading.fadeOut(animDur);
	} else {
		$loading.fadeIn(animDur);
	}
}

function ajaxError(err) {
	console.error(err);
	alert(err.message);
}

function ajaxFail() {
	alert('Server error!');
}

$(function () {

	const ajaxURL = 'api/';
	const loadingFadeInDur = 50;
	const loadingFadeOutDur = 200;

	const $body = $('body');
	initLoadingInd($body);

	//--- posts ---//
	const $postsContent = $body.find('#postsContent');
	$body.find('[data-action="fetch-data"]').click(function () {
		loadingInd($postsContent);
		createUser(ajaxURL);
		// fetchData(ajaxURL);
	});

	function createUser(ajaxURL) {
		const req = {
			method: 'create_user',
			params: {
				user: {
					name: "Semyon",
					email: ""
				}
			}
		};
		$.post(
			ajaxURL,
			req,
			res => (res),
			'json'
		).fail(() => ajaxFail())
			.always(() => {
				loadingInd($postsContent, true, loadingFadeOutDur);
			});
	}

	function fetchData(ajaxURL) {
		const req = {
			method: 'fetch_remote_data',
			params: {}
		};
		$.get(
			ajaxURL,
			req,
			res => fetchDataSuccess(res),
			'json'
		).fail(() => ajaxFail())
			.always(() => {
				loadingInd($postsContent, true, loadingFadeOutDur);
			});
	}

	function fetchDataSuccess(res) {
		if (res.status != 'OK') {
			ajaxError(res);
		} else {

		}
	}
	//--- /posts ---//


	// const componentsConfig = [
	// 	{ view: 'salesmen', ctor: Salesmen, ajaxURL: salesAjaxURL },
	// 	{ view: 'report', ctor: Report, ajaxURL: salesAjaxURL },
	// 	//{ view: 'form', ctor: FormCmp, ajaxURL: '' },
	// ];
	// const cmpInstances = [];

	// for (const cfg of componentsConfig) {
	// 	$body.find(`[data-view="${cfg.view}"]`).each(function () {
	// 		const cmp = new cfg.ctor($(this), cfg.ajaxURL);
	// 		cmp.init();
	// 		cmpInstances.push(cmp);
	// 	});
	// }

});
