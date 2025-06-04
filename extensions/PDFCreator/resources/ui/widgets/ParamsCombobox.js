'use strict';

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};

ext.pdfcreator.ui.ParamsCombobox = function ( cfg ) {
	cfg = cfg || {};

	ext.pdfcreator.ui.ParamsCombobox.parent.call( this, cfg );
};

OO.inheritClass( ext.pdfcreator.ui.ParamsCombobox, OO.ui.ComboBoxInputWidget );

ext.pdfcreator.ui.ParamsCombobox.prototype.onMenuChoose = function ( item ) {
	this.setValue( this.getValue() + ' ' + item.getData() );
};
