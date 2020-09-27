class ParamsContainer {
	_params = {};

	/**
	 * @param {Object} [params] 
	 * @return {Object | void}
	 */
	params(params) {
		if (!params) {
			return Object.assign({}, this._params);
		}
		if (typeof params !== 'object') throw new TypeError(`Invalid params object: ${params}`);
		this._params = Object.assign({}, this._params, params);
	}

	/**
	 * @param {string} property 
	 * @param {*} value 
	 * @return {*}
	 */
	param(property, value) {
		if (typeof value === 'undefined') {
			const val = this._params[property];
			return (val && (typeof val === 'object')) ? Object.assign({}, val) : val;
		}
		const update = {};
		update[property] = Object.assign({}, value);
		this._params = Object.assign({}, this._params, update);
	}
}

class UIComponent {
	static loadingFadeInDur = 50;
	static loadingFadeOutDur = 200;

	$viewContainer;
	loadingItems = new Set;

	constructor($viewContainer) {
		this.$viewContainer = $viewContainer;
	}

	init() { }

	find(selector) {
		return this.$viewContainer.find(selector);
	}

	loading(name, done = false) {
		const $el = this.find(`[data-load="${name}"] > .loading`);
		if (done) {
			this.loadingItems.delete(name);
			$el.fadeOut(UIComponent.loadingFadeOutDur);
		} else {
			this.loadingItems.add(name);
			$el.fadeIn(UIComponent.loadingFadeInDur);
		}
	}

	static loading($el, done = false) {
		const $loading = $el.find('.loading');
		if (done) {
			$loading.fadeOut(UIComponent.loadingFadeOutDur);
		} else {
			$loading.fadeIn(UIComponent.loadingFadeInDur);
		}
	}

	isLoading(name) {
		return this.loadingItems.has(name);
	}

	disable($el, enable = false) {
		$el.prop('disabled', !enable);
	}

	ajaxError(err) {
		UIComponent.ajaxError(err);
	}

	static ajaxError(err) {
		console.error(err);
		alert(err.message);
	}

	ajaxFail() {
		UIComponent.ajaxFail()
	}

	static ajaxFail() {
		alert('Server error!');
	}

	static initLoading($body) {
		$body.find('.loading-wrap:not(:has(> .loading))').append(`
			<div class="loading">
				<div class="loading-overlay d-flex justify-content-center align-items-center">
					<i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i>
				</div>
			</div>
		`);
	}
}


//--- Component classes---//

class CreatePostFormCmp extends UIComponent {
	// $form;
	// $submitBtn;
	form;
	apiUrl;

	constructor($viewContainer, dependencies) {
		super($viewContainer);
		this.setDependencies(dependencies);
	}

	setDependencies(dependencies) {
		if (!(dependencies?.form instanceof FormService)) {
			throw new TypeError('Missing dependency: form');
		}
		this.form = dependencies.form;

		if (!dependencies?.apiUrl) {
			throw new TypeError('Missing dependency: apiUrl');
		}
		this.apiUrl = dependencies.apiUrl;
	}

	init() {
		this.initForm();
	}

	initForm() {
		const params = {
			selectors: { form: 'form', submitBtn: '[type="submit"]' },
			onSubmit: ev => {
				ev.preventDefault();
				if (!this.form.disabled) this.submit();
			}
		}
		this.form.init();
		// const $form = this.find('form');
		// $form.submit(ev => {
		// 	ev.preventDefault();
		// 	this.submit();
		// });
		// this.$submitBtn = $form.find('[type="submit"]');
		// this.$form = $form;
	}

	submit() {
		const loadingName = 'form';
		if (this.isLoading(loadingName)) return;
		this.loading(loadingName);

		this.disable(this.$submitBtn);

		const req = {
			method: 'create_post',
			params: {
				user: this.formGetUser(),
				post: this.formGetPost(),
			},
		};

		$.get(
			this.apiUrl,
			req,
			res => this.submitSuccess(res),
			'json'
		).fail(() => this.ajaxFail())
			.always(() => {
				this.loading(loadingName, true);
				this.disable(this.$submitBtn, true);
			});
	}

	submitSuccess(res) {
		if (res.status != 'OK') {
			if (res.status == 'VALIDATION_FAIL') {
				// TODO:
				alert('Validation failed!');
				console.error(res.data.errors);
			} else {
				this.ajaxError(res);
			}
		} else {
			this.clearForm();
			alert(`
				Success!
				User id: ${res.data.user.id}.
				Post id: ${res.data.post.id}.
			`.replace(/\t/g, ''));
		}
	}

	clearForm() {
		this.$form.find('[data-input]').val('');
	}

	formGetUser() {
		return {
			name: this.$form.find('[data-input="name"]').val(),
			email: this.$form.find('[data-input="email"]').val(),
		};
	}

	formGetPost() {
		return {
			title: this.$form.find('[data-input="title"]').val(),
			body: this.$form.find('[data-input="body"]').val(),
		};
	}
}

class UserStatsCmp extends UIComponent {
	apiUrl;
	$reportRows;

	init() {
		this.$viewContainer.css('min-height', '20rem');
		this.$reportRows = this.find('[data-id="report-rows"]');
		this.getReport();
	}

	getReport() {
		const loadingName = 'report';
		if (this.isLoading(loadingName)) return;
		this.loading(loadingName);

		const req = {
			method: 'user_stats',
			params: {},
		};
		$.get(
			this.apiUrl,
			req,
			res => this.getReportSuccess(res),
			'json'
		).fail(() => this.ajaxFail())
			.always(() => {
				this.loading(loadingName, true);
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
			const tr = `
				<tr>
					<td>${row.user_id}</td>
					<td>${row.monthly_average}</td>
					<td>${row.weekly_average}</td>
				</tr>
			`;
			this.$reportRows.append(tr);
		});
	}
}
//--- /Component classes---//


//--- Services ---//

class FormService {
	_$form;
	_$submitBtn;
	_disabled = false;
	_paramsContainer;
	_defaultParams = {
		selectors: { form: '', submitBtn: '' },
		onSubmit: null,
	};

	constructor(paramsContainer) {
		paramsContainer.params(this._defaultParams);
		this._paramsContainer = paramsContainer;
	}

	get $form() {
		return this.$form;
	}

	get $submitBtn() {
		return this.$submitBtn;
	}

	get disabled() {
		return this._disabled;
	}

	init(params) {
		this.initElements(params?.selectors);
		this.params(params);
	}

	initElements(selectors) {
		if (selectors?.form) {
			const $form = $(selectors.form);
			if (!$form.length) throw new Error(`Selector "${selectors.form}" not found`);
			$form.submit(ev => {
				ev.preventDefault();
				if (!this.disabled) this.submit();
			});
			this.$form = $form;
		}
		if (selectors?.submitBtn) {
			this.$submitBtn = $(selectors.submitBtn);
			if (!this.$submitBtn.length) throw new Error(`Selector "${selectors.form}" not found`);
		}
	}

	params(params) {
		return this._paramsContainer.params(params);
	}

	param(property, value) {
		return this._paramsContainer.param(property, value);
	}

	disable($el, enable = false) {
		this.disabled = !enable;
		this.$submitBtn?.prop('disabled', this.disabled);
	}
}
//--- /Services ---//


$(function () {

	const apiUrl = 'api/';

	const $body = $('body');
	UIComponent.initLoading($body);

	const componentsConfig = [
		{
			view: 'create-post-form',
			ctor: CreatePostFormCmp,
			dependencies: {
				form: new FormService(new ParamsContainer),
				apiUrl,
			}
		},
		{
			view: 'user-stats',
			ctor: UserStatsCmp,
			dependencies: {
				apiUrl,
			}
		},
	];
	const cmpInstances = [];

	for (const cfg of componentsConfig) {
		$body.find(`[data-view="${cfg.view}"]`).each((i, el) => {
			const cmp = new cfg.ctor($(this), cfg.dependencies);
			cmp.init();
			cmpInstances.push(cmp);
		});
	}

	//--- posts ---//
	const $postsContent = $body.find('#postsContent');

	//json
	const $searchBy = $postsContent.find('[data-input="search-by"]');
	const $searchParam = $postsContent.find('[data-input="search-param"]');
	$postsContent.find('[data-action="search"]').click(() => {
		const url = `posts-json.php?${$searchBy.val()}=${$searchParam.val()}`;
		window.open(url, '_blank');
	});

	//fetch data
	$postsContent.find('[data-action="fetch-data"]').click(() => {
		UIComponent.loading($postsContent);
		fetchData(apiURL);
	});

	function fetchData(apiURL) {
		const req = {
			method: 'fetch_remote_data',
			params: {}
		};
		$.get(
			apiURL,
			req,
			res => fetchDataSuccess(res),
			'json'
		).fail(() => ajaxFail())
			.always(() => {
				UIComponent.loading($postsContent, true);
			});
	}

	function fetchDataSuccess(res) {
		if (res.status != 'OK') {
			ajaxError(res);
		} else {
			alert(`
				Success!
				Inserted users: ${res.data.inserted_users}.
				Inserted posts: ${res.data.inserted_posts}.
			`.replace(/\t/g, ''));
		}
	}
	//--- /posts ---//

});
