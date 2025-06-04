'use strict';

ext.pdfcreator.ui.TemplateSelector = function ( cfg ) {
	cfg = cfg || {};
	const templates = require( './exportconfig.json' );
	this.templates = cfg.templates || templates.templates;
	ext.pdfcreator.ui.TemplateSelector.parent.call( this, cfg );
	this.$overlay = cfg.$overlay || true;

	this.$element = $( '<div>' );
	this.appendTemplateWidget();
};

OO.inheritClass( ext.pdfcreator.ui.TemplateSelector, OO.ui.Widget );

ext.pdfcreator.ui.TemplateSelector.prototype.appendTemplateWidget = function () {
	const templatesSelection = [];
	for ( const entry in this.templates ) {
		const item = new OO.ui.MenuOptionWidget( {
			data: this.templates[ entry ],
			label: this.templates[ entry ]
		} );
		templatesSelection.push( item );
	}
	let templateSelectDisabled = false;
	if ( this.templates.length === 1 ) {
		templateSelectDisabled = true;
	}

	this.templateDropdown = new OO.ui.DropdownWidget( {
		menu: {
			items: templatesSelection
		},
		disabled: templateSelectDisabled,
		$overlay: this.$overlay
	} );
	this.templateDropdown.getMenu().selectItemByData( templatesSelection[ 0 ].data );
	this.templateDropdown.getMenu().connect( this, {
		select: 'update'
	} );
	this.$element.append( this.templateDropdown.$element );
};

ext.pdfcreator.ui.TemplateSelector.prototype.update = function () {
	this.emit( 'select' );
};

ext.pdfcreator.ui.TemplateSelector.prototype.getSelectedData = function () {
	return this.templateDropdown.getMenu().findSelectedItem().getData();
};

ext.pdfcreator.ui.TemplateSelector.prototype.selectByData = function ( mode ) {
	this.templateDropdown.getMenu().selectItemByData( mode );
};
