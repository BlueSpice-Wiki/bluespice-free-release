( function ( mw ) {

	mw.ext = mw.ext || {};
	mw.ext.forms = {
		widget: {
			view: {},
			edit: {},
			formElement: {}
		},
		editor: {
			elementGroup: {}
		},
		formElement: {},
		layout: {},
		mixin: {},
		registry: {},
		target: {},
		standalone: {}
	};

	mw.ext.forms.registry.Type = new OO.Registry();
	mw.ext.forms.registry.Target = new OO.Registry();
	mw.ext.forms.registry.ElementGroup = new OO.Registry();

}( mediaWiki ) );
