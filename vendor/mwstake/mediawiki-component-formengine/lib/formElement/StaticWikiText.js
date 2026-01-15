( function ( mw ) {
	mw.ext.forms.formElement.StaticWikiText = function () {};

	OO.inheritClass( mw.ext.forms.formElement.StaticWikiText, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.StaticWikiText.prototype.getElementConfigInternal = function () {
		const def = mw.ext.forms.formElement.StaticWikiText.parent.prototype.getElementConfigInternal.call( this );
		def.wikitext = {
			type: 'textarea',
			name: 'wikitext',
			label: mw.message( 'mwstake-formengine-label-wikitext' ).text(),
			required: true,
			widget_data: {
				tab: 'main'
			}
		};
		return def;
	};

	mw.ext.forms.formElement.StaticWikiText.prototype.getType = function () {
		return 'static_wikitext';
	};

	mw.ext.forms.formElement.StaticWikiText.prototype.getWidgets = function () {
		return mw.ext.forms.widget.view.WikiText;
	};

	mw.ext.forms.formElement.StaticWikiText.prototype.getDisplayName = function () {
		return 'Static Wikitext'; // TODO: i18n
	};

	mw.ext.forms.formElement.StaticWikiText.prototype.getDefaultValue = function () {
		return {
			wikitext: "'''Wikitext''' syntax", // TODO: i18n
			noLayout: false
		};
	};

	mw.ext.forms.registry.Type.register( 'static_wikitext', new mw.ext.forms.formElement.StaticWikiText() );
}( mediaWiki ) );
