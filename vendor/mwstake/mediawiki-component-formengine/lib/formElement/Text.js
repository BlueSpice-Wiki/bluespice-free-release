( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.Text = function() {};

	OO.inheritClass( mw.ext.forms.formElement.Text, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.Text.prototype.getElementConfig = function() {
		return mw.ext.forms.formElement.Text.parent.prototype.getElementConfig.call( this );
	};

	mw.ext.forms.formElement.Text.prototype.getType = function() {
		return "text";
	};

	mw.ext.forms.formElement.Text.prototype.getWidgets = function() {
		return {
			view: mw.ext.forms.widget.view.TextView,
			edit: OO.ui.TextInputWidget
		};
	};

	mw.ext.forms.registry.Type.register( 'text', new mw.ext.forms.formElement.Text() );
	mw.ext.forms.registry.Type.register( "_default", new mw.ext.forms.formElement.Text() );
} )( mediaWiki, jQuery );