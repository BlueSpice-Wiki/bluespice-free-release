( function ( mw ) {
	mw.ext.forms.formElement.FormImport = function () {};

	OO.inheritClass( mw.ext.forms.formElement.FormImport, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.FormImport.prototype.getElementConfig = function () {
		const availableForms = mw.config.get( 'formsAvailableFormsForWidget' );
		return this.returnConfig( {
			formName: {
				type: 'dropdown',
				required: true,
				options: availableForms.partial,
				name: 'formName',
				label: 'Form',
				widget_$overlay: true,
				widget_data: {
					tab: 'main'
				}
			}
		} );
	};

	mw.ext.forms.formElement.FormImport.prototype.getType = function () {
		return 'form_import';
	};

	mw.ext.forms.formElement.FormImport.prototype.getWidgets = function () {
		return mw.ext.forms.widget.edit.FormImport;
	};

	mw.ext.forms.registry.Type.register( 'form_import', new mw.ext.forms.formElement.FormImport() );

}( mediaWiki ) );
