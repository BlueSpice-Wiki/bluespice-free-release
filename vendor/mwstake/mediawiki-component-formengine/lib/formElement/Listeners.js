( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.Listeners = function() {};

	OO.inheritClass( mw.ext.forms.formElement.Listeners, mw.ext.forms.formElement.Multiplier );

	mw.ext.forms.formElement.Listeners.prototype.isSystemElement = function() {
		return true;
	};

	mw.ext.forms.formElement.Listeners.prototype.getType = function() {
		return "listeners";
	};

	mw.ext.forms.formElement.Listeners.prototype.getWidgets = function() {
		return mw.ext.forms.widget.edit.Listeners;
	};

	mw.ext.forms.registry.Type.register( "listeners_widget", new mw.ext.forms.formElement.Listeners() );

} )( mediaWiki, jQuery );