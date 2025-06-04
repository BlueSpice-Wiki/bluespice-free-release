'use strict';

ext.pdfcreator.ui.ModeSelector = function ( cfg ) {
	cfg = cfg || {};
	const modes = require( './modeconfig.json' );
	this.modes = cfg.modes || modes.mode;
	ext.pdfcreator.ui.ModeSelector.parent.call( this, cfg );
	this.$overlay = cfg.$overlay || true;

	this.$element = $( '<div>' );
	this.appendModeWidget();
};

OO.inheritClass( ext.pdfcreator.ui.ModeSelector, OO.ui.Widget );

ext.pdfcreator.ui.ModeSelector.prototype.appendModeWidget = function () {
	const modeSelection = [];
	for ( const entry in this.modes ) {
		const item = new OO.ui.MenuOptionWidget( {
			data: entry,
			label: this.modes[ entry ]
		} );
		modeSelection.push( item );
	}
	let modeSelectDisabled = false;
	if ( Object.keys( this.modes ).length === 1 ) {
		modeSelectDisabled = true;
	}
	this.modeDropdown = new OO.ui.DropdownWidget( {
		menu: {
			items: modeSelection
		},
		disabled: modeSelectDisabled,
		$overlay: this.$overlay
	} );
	this.modeDropdown.getMenu().selectItemByData( modeSelection[ 0 ].data );
	this.modeDropdown.getMenu().connect( this, {
		select: 'update'
	} );
	this.$element.append( this.modeDropdown.$element );
};

ext.pdfcreator.ui.ModeSelector.prototype.update = function () {
	this.emit( 'select' );
};

ext.pdfcreator.ui.ModeSelector.prototype.getSelectedData = function () {
	return this.modeDropdown.getMenu().findSelectedItem().getData();
};

ext.pdfcreator.ui.ModeSelector.prototype.selectByData = function ( mode ) {
	this.modeDropdown.getMenu().selectItemByData( mode );
};
