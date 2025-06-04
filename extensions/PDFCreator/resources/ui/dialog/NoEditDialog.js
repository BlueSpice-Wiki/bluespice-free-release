'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.dialog = ext.pdfcreator.ui.dialog || {};

ext.pdfcreator.ui.dialog.NoEditDialog = function ( cfg ) {
	cfg = cfg || {};
	cfg.classes = [ 'pdfcreator-no-edit-dlg' ];
	ext.pdfcreator.ui.dialog.NoEditDialog.parent.call( this, cfg );
	this.template = cfg.template || {};
	this.errors = cfg.errors;
	this.expanded = false;
};

OO.inheritClass( ext.pdfcreator.ui.dialog.NoEditDialog, OO.ui.ProcessDialog );

ext.pdfcreator.ui.dialog.NoEditDialog.static.name = 'PDFNoEditDialog';
ext.pdfcreator.ui.dialog.NoEditDialog.static.title = mw.message( 'pdfcreator-template-edit-source-dlg-title' ).text();
ext.pdfcreator.ui.dialog.NoEditDialog.static.size = 'large';
ext.pdfcreator.ui.dialog.NoEditDialog.static.actions = [
	{
		action: 'edit',
		label: mw.message( 'pdfcreator-template-edit-source-dlg-action-edit' ).text(),
		flags: [ 'primary', 'progressive' ]
	},
	{
		title: mw.message( 'cancel' ).text(),
		flags: [ 'safe', 'close' ]
	}
];

ext.pdfcreator.ui.dialog.NoEditDialog.prototype.initialize = function () {
	ext.pdfcreator.ui.dialog.NoEditDialog.super.prototype.initialize.apply( this, arguments );
	this.panel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	const label = new OO.ui.LabelWidget( {
		label: mw.message( 'pdfcreator-template-edit-source-dlg-no-compatibility-label', this.template ).text(),
		padded: true,
		expanded: true
	} );

	this.panel.$element.append( label.$element );

	const $table = $( '<table>' ).addClass( 'pdfcreator-no-edit-dlg-table' );
	for ( const error in this.errors ) {
		const $li = $( '<li>' ).text( this.errors[ error ] );
		$table.append( $li );
	}
	this.panel.$element.append( $table );
	this.$body.append( this.panel.$element );
	this.updateSize();
};

ext.pdfcreator.ui.dialog.NoEditDialog.prototype.getActionProcess = function ( action ) {
	return ext.pdfcreator.ui.dialog.NoEditDialog.parent.prototype.getActionProcess.call(
		this, action )
		.next(
			function () {
				if ( action === 'edit' ) {
					this.close();
					this.emit( 'edit' );
				}

				return ext.pdfcreator.ui.dialog.NoEditDialog.parent.prototype.getActionProcess.call(
					this,
					action
				);
			},
			this
		);
};
