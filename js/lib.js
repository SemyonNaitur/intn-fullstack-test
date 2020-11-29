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

class FormTheme {
	_classes = {
		valid: { input: '', msg: '' },
		warning: { input: '', msg: '' },
		invalid: { input: '', msg: '' },
	};
}

class Bootstrap4FormTheme extends FormTheme {

	constructor() {
		super();
		this._classes.valid = { input: 'is-valid', msg: 'valid-feedback' };
		this._classes.invalid = { input: 'is-invalid', msg: 'invalid-feedback' };
	}

	fieldSetValid($el, msg = '') {
		this.fieldSetValidation($el, msg, 'valid');
	}

	fieldSetInvalid($el, msg = '') {
		this.fieldSetValidation($el, msg, 'invalid');
	}

	fieldSetValidation($el, msg = '', state = 'valid') {
		const classes = this._classes[state];
		if (!classes) throw new Error(`Invalid field validation state: ${state}`);
		this.fieldResetValidation($el);
		$el.addClass(classes.input);
		$el.after(`<div class="${classes.msg}">${msg}</div>`);
	}

	fieldResetValidation($el) {
		let classes = '';
		['valid', 'invalid'].forEach(state => classes += this._classes[state].input + ' ');
		$el.removeClass(classes);
		$el.find('~ [class$="-feedback"]').remove();
	}
}

class FormUtil {
	static _id = 0;

	_id;
	_$form;
	_$submitBtn;
	_theme;
	_disabled = false;
	_paramsContainer;
	_defaultParams = {
		selectors: { form: '', submitBtn: '' },
		onSubmit: null,
	};
	_paramHooks = {
		selectors: val => this.initElements(val),
		onSubmit: val => { if (typeof val !== 'function') throw new TypeError('Invalid "onSubmit" callback') },
	}

	constructor(dependencies) {
		this._id = `${this.constructor.name}_${++this.constructor._id}`;

		const p = dependencies?.paramsContainer;
		if (!(p instanceof ParamsUtil)) {
			throw new TypeError('Invalid value for "paramsContainer" dependency')
		}
		p.params(this._defaultParams);
		p.setHooks(this._paramHooks);
		this._paramsContainer = p;

		const t = dependencies?.theme;
		if (!(t instanceof FormTheme)) {
			throw new TypeError('Invalid value for "theme" dependency')
		}
		this._theme = t;
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
		return this._theme;
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
	}

	params(params) {
		return this._paramsContainer.params(params);
	}

	param(property, value) {
		return this._paramsContainer.param(property, value);
	}

	getFields() {
		return this.$form.find(`[data-field]`);
	}

	getField(name) {
		return this.$form.find(`[data-field="${name}"]`);
	}

	disable(disable = true) {
		this._disabled = disable;
		this._$submitBtn?.prop('disabled', disable);
	}

	disableField(name, disable = true) {
		this.getField(name).prop('disabled', disable);
	}

	clear() {
		this._$form.find('[data-field]').val('');
		this.resetErrors();
	}

	setErrors(errors) {
		this.resetErrors();
		for (const fieldName in errors) {
			this.setError(fieldName, errors[fieldName]);
		}
	}

	setError(fieldName, errors) {
		const $el = this.getField(fieldName);
		const msg = this._toHtml(errors.join('\n'));
		this.theme.fieldSetInvalid($el, msg);
	}

	resetErrors() {
		this.getFields().each((i, el) => {
			this.resetError($(el));
		});
	}

	resetError(field) {
		const $field = (typeof field === 'string') ? this.getField(field) : field;
		this.theme.fieldResetValidation($field);
	}

	_toHtml(str) {
		return str?.replace?.(/\n/g, '<br>');
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