( function ( mw ) {
	mw.ext.forms.formElement.BookletLayoutPage = function () {};

	OO.inheritClass( mw.ext.forms.formElement.BookletLayoutPage, mw.ext.forms.formElement.FormLayoutElement );

	mw.ext.forms.formElement.BookletLayoutPage.prototype.getElementConfigInternal = function () {
		const config = mw.ext.forms.formElement.BookletLayoutPage.parent.prototype.getElementConfigInternal.call( this ),
			newConfig = {
				name: config.name,
				label: {
					type: 'text',
					name: 'label',
					label: mw.message( 'mwstake-formengine-label-label' ).text()
				},
				icon: {
					type: 'text',
					name: 'icon',
					label: mw.message( 'mwstake-formengine-label-icon' ).text()
				}
			};
		return this.returnConfig( newConfig );
	};

	mw.ext.forms.formElement.BookletLayoutPage.prototype.getType = function () {
		return 'layout_booklet_page';
	};

	mw.ext.forms.formElement.BookletLayoutPage.prototype.getSubitemConfig = function () {
		return {
			propName: 'widget_items',
			accepts: [ 'input', 'static' ]
		};
	};

	mw.ext.forms.formElement.BookletLayoutPage.prototype.isHidden = function () {
		return true;
	};

	mw.ext.forms.formElement.BookletLayoutPage.prototype.getDisplayName = function () {
		return mw.message( 'mwstake-formengine-form-formelement-booklet-layout-page' ).text();
	};

	mw.ext.forms.formElement.BookletLayoutPage.prototype.isSubitem = function () {
		return true;
	};

	mw.ext.forms.registry.Type.register( 'layout_booklet_page', new mw.ext.forms.formElement.BookletLayoutPage() );

}( mediaWiki ) );
