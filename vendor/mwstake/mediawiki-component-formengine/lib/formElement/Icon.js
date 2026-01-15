( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.Icon = function() {};

	OO.inheritClass( mw.ext.forms.formElement.Icon, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.Icon.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.Icon.parent.prototype.getElementConfigInternal.call( this );
		config.noLayout.value = true;
		config.icon = {
			type: 'text',
			name: 'icon',
			label: mw.message( 'mwstake-formengine-label-icon' ).text(),
			widget_data: {
				tab: 'main'
			}
		};

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Icon.prototype.getType = function() {
		return "icon";
	};

	mw.ext.forms.formElement.Icon.prototype.getWidgets = function() {
		return OO.ui.IconWidget;
	};

	mw.ext.forms.formElement.Icon.prototype.getDefaultValue = function() {
		return {
			icon: 'info'
		};
	};

	mw.ext.forms.registry.Type.register( "icon", new mw.ext.forms.formElement.Icon() );

} )( mediaWiki, jQuery );
