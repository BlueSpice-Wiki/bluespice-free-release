( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.HorizontalLayout = function() {};

	OO.inheritClass( mw.ext.forms.formElement.HorizontalLayout, mw.ext.forms.formElement.FormLayoutElement );

	mw.ext.forms.formElement.HorizontalLayout.prototype.getType = function() {
		return "layout_horizontal";
	};

	mw.ext.forms.formElement.HorizontalLayout.prototype.getWidgets = function() {
		return OO.ui.HorizontalLayout;
	};

	mw.ext.forms.formElement.HorizontalLayout.prototype.getSubitemConfig = function() {
		return {
			propName: 'layoutItems',
			accepts: [ 'input', 'static' ]
		};
	};

	mw.ext.forms.registry.Type.register( "layout_horizontal", new mw.ext.forms.formElement.HorizontalLayout() );

} )( mediaWiki, jQuery );
