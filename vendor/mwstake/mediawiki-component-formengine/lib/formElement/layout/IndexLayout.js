( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.IndexLayout = function() {};

	OO.inheritClass( mw.ext.forms.formElement.IndexLayout, mw.ext.forms.formElement.FormLayoutElement );

	mw.ext.forms.formElement.IndexLayout.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.IndexLayout.parent.prototype.getElementConfigInternal.call( this );
		config.framed = {
			type: 'checkbox',
			value: true,
			name: 'framed',
			label: mw.message( 'mwstake-formengine-label-framed' ).text()
		};
		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.IndexLayout.prototype.getSubitemConfig = function() {
		return {
			propName: 'tabs',
			accepts: [ 'layout_index_tab' ]
		};
	};

	// DISABLED FOR EDITOR FOR NOW
	mw.ext.forms.formElement.IndexLayout.prototype.isHidden = function() {
		return true;
	};

	mw.ext.forms.formElement.IndexLayout.prototype.getType = function() {
		return "layout_index";
	};

	mw.ext.forms.formElement.IndexLayout.prototype.getWidgets = function() {
		return OO.ui.IndexLayout;
	};

	mw.ext.forms.registry.Type.register( "layout_index", new mw.ext.forms.formElement.IndexLayout() );

} )( mediaWiki, jQuery );
