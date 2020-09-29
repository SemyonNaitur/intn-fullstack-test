//--- Utils ---//

class ParamsUtil {
	_params = {};
	_hooks = {};

	setHooks(hooks) {
		for (const k in hooks) {
			const fn = hooks[k];
			if (typeof fn !== 'function') throw new TypeError(`Invalid function for "${k}" hook`);
		}
		this._hooks = hooks;
	}

	/**
	 * @param {Object} [params] 
	 * @return {Object | void}
	 */
	params(params) {
		if (!params) {
			return this._clone();
		}
		if (typeof params !== 'object') throw new TypeError(`Invalid params object: ${params}`);
		for (const k in params) {
			this.param(k, params[k]);
		}
	}

	/**
	 * @param {string} property 
	 * @param {*} value 
	 * @return {*}
	 */
	param(property, value) {
		if (typeof value === 'undefined') {
			return this._cloneVal(this._params[property]);
		}
		value = this._cloneVal(value);
		this._hooks[property]?.(value);
		this._params = { ...this._params, [property]: value };
	}

	_clone(params) {
		params = params || this._params;
		const ret = {};
		for (const k in params) {
			ret[k] = this._cloneVal(params[k]);
		}
		return ret;
	}

	_cloneVal(val) {
		if (Array.isArray(val)) return [...val];
		return (val && typeof val === 'object') ? { ...val } : val;
	}
}

class FormUtil {
	static _id = 0;
	static _themes = {
		bootstrap4: { errMsg: 'invalid-feedback' },
	};

	_id;
	_$form;
	_$submitBtn;
	_theme;
	_disabled = false;
	_paramsContainer;
	_defaultParams = {
		selectors: { form: '', submitBtn: '' },
		onSubmit: null,
		theme: 'bootstrap4',
	};
	_paramHooks = {
		selectors: val => this.initElements(val),
		onSubmit: val => { if (typeof val !== 'function') throw new TypeError('Invalid "onSubmit" callback') },
		theme: val => this.initTheme(val),
	}

	constructor(paramsContainer) {
		this._id = `${this.constructor.name}_${++this.constructor._id}`;
		paramsContainer.params(this._defaultParams);
		paramsContainer.setHooks(this._paramHooks);
		this._paramsContainer = paramsContainer;
	}

	get $form() {
		return this._$form;
	}

	get $submitBtn() {
		return this._$submitBtn;
	}

	get disabled() {
		return this._disabled;
	}

	get theme() {
		const name = this.param('theme');
		return this.constructor._themes[name];
	}

	init(params) {
		this.params(params);
	}

	initElements(selectors) {
		this.initFormElement(selectors?.form);

		this._$submitBtn = $(selectors?.submitBtn);
		if (!this._$submitBtn.length) throw new Error(`Selector "${selectors.submitBtn}" not found`);
	}

	initFormElement(selector) {
		const $form = $(selector);
		if (!$form.length) throw new Error(`Selector "${selector}" not found`);

		$form.attr('data-id', this._id);
		if (!$form.attr('id')) {
			$form.attr('id', this._id);
		}

		$form.submit(ev => {
			this.param('onSubmit')?.(ev);
		});

		this._$form = $form;

		this.initInputs();
	}

	initInputs($form) {
		$form = $form || this.$form;
		$form.find('[data-input]').each((i, el) => {
			const $el = $(el);
			const name = $el.attr('data-input');
			let $err = $el.siblings('.invalid-feedback');
			if (!$err.length) {
				$err = $('<div class="invalid-feedback">');
				$el.after($err);
			}
			$err.attr('data-error', name);
		});
	}

	params(params) {
		return this._paramsContainer.params(params);
	}

	param(property, value) {
		return this._paramsContainer.param(property, value);
	}

	getInput(name) {
		return this.$form.find(`[data-input="${name}"]`);
	}

	disable(disable = true) {
		this._disabled = disable;
		this._$submitBtn?.prop('disabled', disable);
	}

	disableInput(name, disable = true) {
		this.getInput(name).prop('disabled', disable);
	}

	clear() {
		this._$form.find('[data-input]').val('');
	}
}
//--- /Utils ---//

class UIComponent {
	static loadingFadeInDur = 50;
	static loadingFadeOutDur = 200;

	_$viewContainer;
	_loadingItems = new Set;

	constructor($viewContainer) {
		this._$viewContainer = $viewContainer;
	}

	init() { }

	find(selector) {
		return this._$viewContainer.find(selector);
	}

	loading(name, done = false) {
		const $el = this.find(`[data-load="${name}"] > .loading`);
		if (done) {
			this._loadingItems.delete(name);
			$el.fadeOut(UIComponent.loadingFadeOutDur);
		} else {
			this._loadingItems.add(name);
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
		return this._loadingItems.has(name);
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