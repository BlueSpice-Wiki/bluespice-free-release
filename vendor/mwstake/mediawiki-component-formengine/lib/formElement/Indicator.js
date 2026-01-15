( function ( mw ) {
	mw.ext.forms.formElement.Indicator = function () {};

	OO.inheritClass( mw.ext.forms.formElement.Indicator, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.Indicator.prototype.getElementConfig = function () {
		const config = mw.ext.forms.formElement.Indicator.parent.prototype.getElementConfigInternal.call( this );
		config.noLayout.value = true;
		config.indicator = {
			type: 'text',
			name: 'indicator',
			label: mw.message( 'mwstake-formengine-label-indicator' ).text(),
			widget_data: {
				tab: 'main'
			}
		};

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Indicator.prototype.getType = function () {
		return 'indicator';
	};

	mw.ext.forms.formElement.Indicator.prototype.getWidgets = function () {
		return OO.ui.IndicatorWidget;
	};

	mw.ext.forms.formElement.Indicator.prototype.getDefaultValue = function () {
		return {
			indicator: 'required' // TODO: i18n
		};
	};

	mw.ext.forms.registry.Type.register( 'indicator', new mw.ext.forms.formElement.Indicator() );

}( mediaWiki ) );
