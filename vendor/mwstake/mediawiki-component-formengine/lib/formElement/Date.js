( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.Date = function() {};

	OO.inheritClass( mw.ext.forms.formElement.Date, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.Date.prototype.getType = function() {
		return "date";
	};

	mw.ext.forms.formElement.Date.prototype.getWidgets = function() {
		return {
			view: mw.ext.forms.widget.view.TextView,
			edit: mw.widgets.DateInputWidget
		};
	};

	mw.ext.forms.registry.Type.register( "date", new mw.ext.forms.formElement.Date() );

} )( mediaWiki, jQuery );