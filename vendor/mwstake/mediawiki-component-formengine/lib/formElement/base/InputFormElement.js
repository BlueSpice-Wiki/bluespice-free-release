( function ( mw ) {
	mw.ext.forms.formElement.InputFormElement = function () {};

	OO.inheritClass( mw.ext.forms.formElement.InputFormElement, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.InputFormElement.prototype.getGroup = function () {
		return 'input';
	};

	mw.ext.forms.formElement.InputFormElement.prototype.getElementConfigInternal = function () {
		const config = mw.ext.forms.formElement.InputFormElement.parent.prototype.getElementConfigInternal.call( this );
		config.value = {
			type: 'text',
			name: 'value',
			label: mw.message( 'mwstake-formengine-label-default-value' ).text()
		};

		config.required = {
			type: 'checkbox',
			name: 'required',
			label: mw.message( 'mwstake-formengine-label-required' ).text()
		};

		return config;
	};
}( mediaWiki ) );
