( function ( mw ) {
	mw.ext.forms.formElement.FormLayoutElement = function () {};

	OO.inheritClass( mw.ext.forms.formElement.FormLayoutElement, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.FormLayoutElement.prototype.getGroup = function () {
		return 'layout';
	};

	mw.ext.forms.formElement.FormLayoutElement.prototype.getElementConfigInternal = function () {
		const config = mw.ext.forms.formElement.FormLayoutElement.parent.prototype.getElementConfigInternal.call( this );
		delete ( config.label );
		delete ( config.help );
		delete ( config.noLayout );
		delete ( config.listeners );
		delete ( config.editableOn );
		return config;
	};

}( mediaWiki ) );
