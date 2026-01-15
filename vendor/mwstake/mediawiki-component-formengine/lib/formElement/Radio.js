( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.Radio = function() {};

	OO.inheritClass( mw.ext.forms.formElement.Radio, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.Radio.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.Radio.parent.prototype.getElementConfigInternal.call( this );
		config.value.type = 'checkbox';
		config.value.label = mw.message( 'mwstake-formengine-label-sel-by-default' ).text();
		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Radio.prototype.getType = function() {
		return "radio";
	};

	mw.ext.forms.formElement.Radio.prototype.getWidgets = function() {
		return {
			view: mw.ext.forms.widget.view.BooleanView,
			edit: mw.ext.forms.widget.edit.Radio
		};
	};

	mw.ext.forms.registry.Type.register( "radio", new mw.ext.forms.formElement.Radio() );
} )( mediaWiki, jQuery );