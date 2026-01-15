( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.IndexLayoutTab = function() {};

	OO.inheritClass( mw.ext.forms.formElement.IndexLayoutTab, mw.ext.forms.formElement.FormLayoutElement );

	mw.ext.forms.formElement.IndexLayoutTab.prototype.getElementConfig = function() {
		var config = {};
		config.name = {
			type: 'text',
			name: 'name',
			label: mw.message( 'mwstake-formengine-label-name' ).text(),
			required: true
		};
		config.label = {
			type: 'text',
			name: 'label',
			label: mw.message( 'mwstake-formengine-label-label' ).text()
		};
		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.IndexLayoutTab.prototype.getSubitemConfig = function() {
		return {
			propName: 'widget_items',
			accepts: [ 'input', 'static' ]
		};
	};

	mw.ext.forms.formElement.IndexLayoutTab.prototype.getType = function() {
		return "layout_index_tab";
	};

	mw.ext.forms.formElement.IndexLayoutTab.prototype.isHidden = function() {
		return true;
	};

	mw.ext.forms.formElement.IndexLayoutTab.prototype.getDisplayName = function() {
		return mw.message( 'mwstake-formengine-form-formelement-index-layout-tab' ).text();
	};

	mw.ext.forms.formElement.IndexLayoutTab.prototype.isSubitem = function() {
		return true;
	};

	mw.ext.forms.registry.Type.register( "layout_index_tab", new mw.ext.forms.formElement.IndexLayoutTab() );

} )( mediaWiki, jQuery );
