( function ( mw ) {
	// TODO: This will eventually get replaced with VE field
	mw.ext.forms.formElement.WikiText = function () {};

	OO.inheritClass( mw.ext.forms.formElement.WikiText, mw.ext.forms.formElement.TextArea );

	mw.ext.forms.formElement.WikiText.prototype.getType = function () {
		return 'wikitext';
	};

	mw.ext.forms.formElement.WikiText.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.WikiText,
			edit: OO.ui.MultilineTextInputWidget
		};
	};

	mw.ext.forms.formElement.WikiText.prototype.getDisplayName = function () {
		return mw.message( 'mwstake-formengine-form-formelement-wikitext-input' ).text();
	};

	mw.ext.forms.formElement.WikiText.prototype.getIcon = function () {
		return 'wikiText';
	};

	mw.ext.forms.registry.Type.register( 'wikitext', new mw.ext.forms.formElement.WikiText() );
}( mediaWiki ) );
