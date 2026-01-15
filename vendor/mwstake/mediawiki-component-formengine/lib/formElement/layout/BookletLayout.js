( function ( mw ) {
	mw.ext.forms.formElement.BookletLayout = function () {};

	OO.inheritClass( mw.ext.forms.formElement.BookletLayout, mw.ext.forms.formElement.FormLayoutElement );

	mw.ext.forms.formElement.BookletLayout.prototype.getElementConfigInternal = function () {
		const config = mw.ext.forms.formElement.BookletLayout.parent.prototype.getElementConfigInternal.call( this );
		config.outlined = {
			type: 'checkbox',
			value: true,
			name: 'outlined',
			label: mw.message( 'mwstake-formengine-label-outlined' ).text()
		};
		config.framed = {
			type: 'checkbox',
			value: true,
			name: 'framed',
			label: mw.message( 'mwstake-formengine-label-framed' ).text()
		};
		return this.returnConfig( config );
	};

	// DISABLED FOR EDITOR FOR NOW
	mw.ext.forms.formElement.BookletLayout.prototype.isHidden = function () {
		return true;
	};

	mw.ext.forms.formElement.BookletLayout.prototype.getType = function () {
		return 'layout_booklet';
	};

	mw.ext.forms.formElement.BookletLayout.prototype.getWidgets = function () {
		return OO.ui.BookletLayout;
	};

	mw.ext.forms.formElement.BookletLayout.prototype.getSubitemConfig = function () {
		return {
			propName: 'pages',
			accepts: [ 'layout_booklet_page' ]
		};
	};

	mw.ext.forms.registry.Type.register( 'layout_booklet', new mw.ext.forms.formElement.BookletLayout() );

}( mediaWiki, jQuery ) );
