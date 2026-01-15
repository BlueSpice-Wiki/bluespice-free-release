( function ( mw ) {
	mw.ext.forms.formElement.ComboBox = function () {};

	OO.inheritClass( mw.ext.forms.formElement.ComboBox, mw.ext.forms.formElement.Dropdown );

	mw.ext.forms.formElement.ComboBox.prototype.getType = function () {
		return 'combobox';
	};

	mw.ext.forms.formElement.ComboBox.prototype.getWidgets = function () {
		return {
			view: mw.ext.forms.widget.view.OptionView,
			edit: OO.ui.ComboBoxInputWidget
		};
	};

	mw.ext.forms.registry.Type.register( 'combobox', new mw.ext.forms.formElement.ComboBox() );

}( mediaWiki ) );
