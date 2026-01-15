( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.CustomFormProps = function() {};

	OO.inheritClass( mw.ext.forms.formElement.CustomFormProps, mw.ext.forms.formElement.Multiplier );

	mw.ext.forms.formElement.CustomFormProps.prototype.isSystemElement = function() {
		return true;
	};

	mw.ext.forms.formElement.CustomFormProps.prototype.getType = function() {
		return "custom_form_props";
	};

	mw.ext.forms.formElement.CustomFormProps.prototype.getWidgets = function() {
		return mw.ext.forms.widget.edit.CustomFormProps;
	};

	mw.ext.forms.registry.Type.register( "custom_form_props", new mw.ext.forms.formElement.CustomFormProps() );

} )( mediaWiki, jQuery );