( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.Button = function() {};

	OO.inheritClass( mw.ext.forms.formElement.Button, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.Button.static.widgets = "OO.ui.ButtonWidget";

	mw.ext.forms.formElement.Button.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.Button.parent.prototype.getElementConfigInternal.call( this );
		config.label.name = 'widget_label';
		config.noLayout.value = true;
		config.noLayout.hidden = true;
		config.icon = {
			type: 'text',
			name: 'icon',
			label: mw.message( 'mwstake-formengine-label-icon' ).text(),
			widget_data: {
				tab: 'styling'
			}
		};
		config.indicator = {
			type: 'text',
			name: 'indicator',
			label:  mw.message( 'mwstake-formengine-label-indicator' ).text(),
			widget_data: {
				tab: 'styling'
			}
		};
		config.framed = {
			type: 'checkbox',
			name: 'framed',
			value: true,
			label:  mw.message( 'mwstake-formengine-label-framed' ).text(),
			widget_data: {
				tab: 'styling'
			}
		};

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Button.prototype.getType = function() {
		return "button";
	};

	mw.ext.forms.formElement.Button.prototype.getWidgets = function() {
		return OO.ui.ButtonWidget;
	};

	mw.ext.forms.formElement.Button.prototype.getDefaultValue = function() {
		return {
			widget_label: "Button" //TODO: i18n
		};
	};

	mw.ext.forms.registry.Type.register( "button", new mw.ext.forms.formElement.Button() );

} )( mediaWiki, jQuery );
