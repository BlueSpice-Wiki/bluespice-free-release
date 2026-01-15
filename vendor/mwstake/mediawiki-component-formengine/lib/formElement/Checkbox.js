( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.Checkbox = function() {};

	OO.inheritClass( mw.ext.forms.formElement.Checkbox, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.Checkbox.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.Checkbox.parent.prototype.getElementConfigInternal.call( this );

		config.value.type = 'checkbox';
		config.value.label =  mw.message( 'mwstake-formengine-label-sel-by-default' ).text();
		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Checkbox.prototype.getType = function() {
		return "checkbox";
	};

	mw.ext.forms.formElement.Checkbox.prototype.getWidgets = function() {
		return {
			view: mw.ext.forms.widget.view.BooleanView,
			edit: mw.ext.forms.widget.edit.Checkbox
		};
	};

	mw.ext.forms.registry.Type.register( "checkbox", new mw.ext.forms.formElement.Checkbox() );

} )( mediaWiki, jQuery );