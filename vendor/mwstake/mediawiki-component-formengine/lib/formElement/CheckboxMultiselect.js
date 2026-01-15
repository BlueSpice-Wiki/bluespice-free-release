( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.CheckboxMultiselect = function() {};

	OO.inheritClass( mw.ext.forms.formElement.CheckboxMultiselect, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.CheckboxMultiselect.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.CheckboxMultiselect.parent.prototype.getElementConfigInternal.call( this );

		config.horizontal = {
			name: 'horizontal',
			type: 'checkbox',
			label: mw.message( 'mwstake-formengine-label-checkbox-horizontal-layout' ).text()
		};

		config.options = {
			type: 'multiplier',
			name: 'options',
			label: mw.message( 'mwstake-formengine-label-options' ).text(),
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

		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.CheckboxMultiselect.prototype.getType = function() {
		return "checkbox_multiselect";
	};

	mw.ext.forms.formElement.CheckboxMultiselect.prototype.getWidgets = function() {
		return {
			view: mw.ext.forms.widget.view.CheckboxMultiselectView,
			edit: mw.ext.forms.widget.edit.CheckboxMultiselect
		};
	};

	mw.ext.forms.registry.Type.register( "checkbox_multiselect", new mw.ext.forms.formElement.CheckboxMultiselect() );

} )( mediaWiki, jQuery );
