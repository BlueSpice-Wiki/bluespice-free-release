( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.RadioMultiselect = function() {};

	OO.inheritClass( mw.ext.forms.formElement.RadioMultiselect, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.RadioMultiselect.prototype.getElementConfigInternal = function() {
		var config = mw.ext.forms.formElement.RadioMultiselect.parent.prototype.getElementConfigInternal.call( this );
		config.noDefaultValue = {
			type: 'checkbox',
			name: 'noDefaultValue',
			label: mw.message( 'mwstake-formengine-no-default-value' ).text(),
			widget_data: {
				tab: 'styling'
			}
		};
		config.horizontal = {
			name: 'horizontal',
			type: 'checkbox',
			label: mw.message( 'mwstake-formengine-label-checkbox-horizontal-layout' ).text(),
			widget_data: {
				tab: 'styling'
			}
		};

		config.options = {
			type: 'multiplier',
			name: 'options',
			noLayout: true,
			base: [ {
				type: 'text',
				name: 'data',
				label: mw.message( 'mwstake-formengine-label-data' ).text(),
				required: true
			}, {
				type: 'text',
				name: 'label',
				label: mw.message( 'mwstake-formengine-label-label' ).text(),
				required: true
			} ],
			widget_data: {
				tab: 'options'
			}
		};
		return config;
	};

	mw.ext.forms.formElement.RadioMultiselect.prototype.getType = function() {
		return "radio_multiselect";
	};

	mw.ext.forms.formElement.RadioMultiselect.prototype.getWidgets = function() {
		return {
			view: mw.ext.forms.widget.view.RadioSelectView,
			edit: mw.ext.forms.widget.edit.RadioSelectInputWidget
		};
	};

	mw.ext.forms.formElement.RadioMultiselect.prototype.getDefaultValue = function() {
		return {
			options: [ { data: 'option1', label: 'Option 1' } ] //TODO: i18n
		};
	};

	mw.ext.forms.registry.Type.register( "radio_multiselect", new mw.ext.forms.formElement.RadioMultiselect() );
} )( mediaWiki, jQuery );
