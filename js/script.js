class UIComponent {
	apiEndpoint;
	$viewContainer;
	loadingFadeInDur = 50;
	loadingFadeOutDur = 200;
	loadingItems = new Set;

	constructor($viewContainer, apiEndpoint = '') {
		this.$viewContainer = $viewContainer;
		this.apiEndpoint = apiEndpoint;
	}

	init() { }

	find(selector) {
		return this.$viewContainer.find(selector);
	}

	loading(name, done = false) {
		const $el = this.find(`[data-load="${name}"] > .loading`);
		if (done) {
			this.loadingItems.delete(name);
			$el.fadeOut(this.loadingFadeOutDur);
		} else {
			this.loadingItems.add(name);
			$el.fadeIn(this.loadingFadeInDur);
		}
	}

	isLoading(name) {
		return this.loadingItems.has(name);
	}

	disable($el, enable = false) {
		$el.prop('disabled', !enable);
	}

	ajaxError(err) {
		console.error(err);
		alert(err.message);
	}

	ajaxFail() {
		alert('Server error!');
	}
}

//--- Component classes---//

class CreatePostFormCmp extends UIComponent {
	$form;
	$submitBtn;

	init() {
		this.initForm();
	}

	initForm() {
		const $form = this.find('form');
		$form.submit(ev => {
			ev.preventDefault();
			this.submit();
		});
		this.$submitBtn = $form.find('[type="submit"]');
		this.$form = $form;
	}

	submit() {
		const loadingName = 'form';
		if (this.isLoading(loadingName)) {
			return;
		}
		this.loading(loadingName);
		this.disable(this.$submitBtn);

		const req = {
			method: 'create_post',
			params: {
				user: this.formGetUser(),
				post: this.formGetPost(),
			},
		};

		$.post(
			this.apiEndpoint,
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
			this.ajaxError(res);
		} else {
			this.clearForm();
			alert(`
				Success!
				User id: ${res.data.user.id}.
				Post id: ${res.data.post.id}.
			`);
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
//--- /Component classes---//


function initLoadingInd($body) {
	$body.find('.loading-wrap:not(:has(> .loading))').append(`
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

	const apiURL = 'api/';
	const loadingFadeInDur = 50;
	const loadingFadeOutDur = 200;

	const $body = $('body');
	initLoadingInd($body);

	const componentsConfig = [
		{ view: 'create-post-form', ctor: CreatePostFormCmp, apiEndpoint: apiURL },
	];
	const cmpInstances = [];

	for (const cfg of componentsConfig) {
		$body.find(`[data-view="${cfg.view}"]`).each((i, el) => {
			const cmp = new cfg.ctor($(this), cfg.apiEndpoint);
			cmp.init();
			cmpInstances.push(cmp);
		});
	}

	//--- posts ---//
	const $postsContent = $body.find('#postsContent');
	$body.find('[data-action="fetch-data"]').click(() => {
		loadingInd($postsContent);
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
				loadingInd($postsContent, true, loadingFadeOutDur);
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
			`);
		}
	}
	//--- /posts ---//

});
