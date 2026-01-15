( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.CategoryMultiselect = function() {};

	OO.inheritClass( mw.ext.forms.formElement.CategoryMultiselect, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.CategoryMultiselect.prototype.getType = function() {
		return "category_multiselect";
	};

	mw.ext.forms.formElement.CategoryMultiselect.prototype.getWidgets = function() {
		return {
			view: mw.ext.forms.widget.view.CategoryMultiselectView,
			edit: mw.ext.forms.widget.edit.CategoryMultiselect,
		};
	};

	mw.ext.forms.registry.Type.register( "category_multiselect", new mw.ext.forms.formElement.CategoryMultiselect() );

} )( mediaWiki, jQuery );
