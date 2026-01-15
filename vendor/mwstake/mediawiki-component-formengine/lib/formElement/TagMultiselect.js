( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.TagMultiselect = function() {};

	OO.inheritClass( mw.ext.forms.formElement.TagMultiselect, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.TagMultiselect.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.TagMultiselect.parent.prototype.getElementConfigInternal.call( this );

		config.inputPosition = {
			type: 'radio_multiselect',
			name: 'inputPosition',
			noLayout: false,
			label: mw.message( 'mwstake-formengine-label-input-position' ).text(),
			options: [
				{ data: 'inline' },
				{ data: 'outline' },
			]
		};

		config.allowArbitrary =  {
			type: 'checkbox',
			name: 'allowArbitrary',
			label: mw.message( 'mwstake-formengine-label-allow-arbitrary' ).text(),
			value: true
		};
		config.allowedValues =  {
			type: 'tag',
			name: 'allowedValue',
			label: mw.message( 'mwstake-formengine-label-allowed-values' ).text(),
			widget_allowArbitrary: true
		};

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.TagMultiselect.prototype.getType = function() {
		return "tag";
	};

	mw.ext.forms.formElement.TagMultiselect.prototype.getWidgets = function() {
		return {
			view: mw.ext.forms.widget.view.MenuTagMultiselectView,
			edit: OO.ui.TagMultiselectWidget
		};
	};

	mw.ext.forms.registry.Type.register( "tag", new mw.ext.forms.formElement.TagMultiselect() );

} )( mediaWiki, jQuery );
