'use strict';

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};

ext.pdfcreator.ui.SelectTemplateSectionWidget = function ( cfg ) {
	cfg = cfg || {};

	ext.pdfcreator.ui.SelectTemplateSectionWidget.parent.call( this, cfg );

	this.section = cfg.section;
	this.selected = cfg.selected || false;
	this.sectionLabel = cfg.label || '';
	this.$element = $( '<div>' ).addClass( 'pdfcreator-select-template-section-widget' );
	this.buildWidget();
};

OO.inheritClass( ext.pdfcreator.ui.SelectTemplateSectionWidget, OO.ui.Widget );

ext.pdfcreator.ui.SelectTemplateSectionWidget.prototype.buildWidget = function () {
	this.activeSelection = new OO.ui.CheckboxInputWidget( {
		selected: this.selected
	} );

	const activeLayout = new OO.ui.FieldLayout( this.activeSelection, {
		label: this.sectionLabel,
		align: 'inline'
	} );

	this.configureButton = new OO.ui.ButtonWidget( {
		framed: false,
		label: mw.message( 'pdfcreator-select-template-widget-configure-btn-label' ).text(),
		indicator: 'down'
	} );
	this.configureButton.connect( this, {
		click: function () {
			this.emit( 'configureSection', this.section );
		}
	} );
	this.$element.append( activeLayout.$element );
	this.$element.append( this.configureButton.$element );
};

ext.pdfcreator.ui.SelectTemplateSectionWidget.prototype.getSection = function () {
	return this.section;
};

ext.pdfcreator.ui.SelectTemplateSectionWidget.prototype.isSelected = function () {
	return this.activeSelection.isSelected();
};

ext.pdfcreator.ui.SelectTemplateSectionWidget.prototype.setSelected = function ( selected ) {
	this.activeSelection.setSelected( selected );
};
