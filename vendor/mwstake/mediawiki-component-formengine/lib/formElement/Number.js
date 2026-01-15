( function ( mw ) {
	mw.ext.forms.formElement.Number = function () {};

	OO.inheritClass( mw.ext.forms.formElement.Number, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.Number.prototype.getElementConfig = function () {
		const config = mw.ext.forms.formElement.Number.parent.prototype.getElementConfigInternal.call( this );
		config.min = {
			type: 'number',
			name: 'min',
			value: 1,
			label: mw.message( 'mwstake-formengine-label-min' ).text(),
			widget_data: {
				tab: 'main'
			}
		};
		config.max = {
			type: 'number',
			name: 'max',
			value: 9999,
			label: mw.message( 'mwstake-formengine-label-max' ).text(),
			widget_data: {
				tab: 'main'
			}
		};

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.Number.prototype.getType = function () {
		return 'number';
	};

	mw.ext.forms.formElement.Number.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.TextView,
			edit: OO.ui.NumberInputWidget
		};
	};

	mw.ext.forms.registry.Type.register( 'number', new mw.ext.forms.formElement.Number() );
}( mediaWiki ) );
