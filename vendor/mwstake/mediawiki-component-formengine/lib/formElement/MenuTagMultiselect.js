( function ( mw ) {
	mw.ext.forms.formElement.MenuTagMultiselect = function () {};

	OO.inheritClass( mw.ext.forms.formElement.MenuTagMultiselect, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.MenuTagMultiselect.prototype.getElementConfig = function () {
		const config = mw.ext.forms.formElement.MenuTagMultiselect.parent.prototype.getElementConfigInternal.call( this );
		config.options = {
			type: 'multiplier',
			name: 'options',
			noLayout: true,
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

	mw.ext.forms.formElement.MenuTagMultiselect.prototype.getType = function () {
		return 'menutag_multiselect';
	};

	mw.ext.forms.formElement.MenuTagMultiselect.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.MenuTagMultiselectView,
			edit: OO.ui.MenuTagMultiselectWidget
		};
	};

	mw.ext.forms.formElement.MenuTagMultiselect.prototype.isInput = function () {
		return true;
	};

	mw.ext.forms.registry.Type.register( 'menutag_multiselect', new mw.ext.forms.formElement.MenuTagMultiselect() );

}( mediaWiki ) );
