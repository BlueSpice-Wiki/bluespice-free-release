'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.dialog = ext.pdfcreator.ui.dialog || {};

require( './../../api/Api.js' );

ext.pdfcreator.ui.dialog.ExportDialog = function ( cfg ) {
	cfg = cfg || {};
	ext.pdfcreator.ui.dialog.ExportDialog.super.call( this, cfg );

	this.modes = cfg.modes || [];
	this.templates = cfg.templates || [];
	this.defaultTemplates = cfg.defaultTemplates || [];
};

OO.inheritClass( ext.pdfcreator.ui.dialog.ExportDialog, OO.ui.ProcessDialog );

ext.pdfcreator.ui.dialog.ExportDialog.static.name = 'PDFExportDialog';
ext.pdfcreator.ui.dialog.ExportDialog.static.title = mw.message( 'pdfcreator-dialog-export-title' ).text();

ext.pdfcreator.ui.dialog.ExportDialog.static.size = 'medium';

ext.pdfcreator.ui.dialog.ExportDialog.static.actions = [
	{
		action: 'save',
		label: mw.message( 'pdfcreator-dialog-export-action-export-label' ).text(),
		flags: [ 'primary', 'progressive' ]
	},
	{
		label: mw.message( 'cancel' ).text(),
		flags: 'safe'
	}
];

ext.pdfcreator.ui.dialog.ExportDialog.prototype.initialize = function () {
	ext.pdfcreator.ui.dialog.ExportDialog.super.prototype.initialize.apply( this, arguments );
	this.panel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );
	this.appendMode();
	this.appendTemplates();

	this.$body.append( this.panel.$element );
	this.updateSize();
};

ext.pdfcreator.ui.dialog.ExportDialog.prototype.appendMode = function () {
	this.modeDropdown = new ext.pdfcreator.ui.ModeSelector( {
		modes: this.modes,
		$overlay: this.$overlay
	} );
	this.mode = this.modeDropdown.getSelectedData();
	this.modeDropdown.connect( this, {
		select: 'updatePanel'
	} );
	const modeField = new OO.ui.FieldLayout( this.modeDropdown, {
		label: mw.message( 'pdfcreator-dialog-export-mode-selection-label' ).text(),
		align: 'top'
	} );
	this.panel.$element.append( modeField.$element );
	this.pluginPanel = new OO.ui.PanelLayout( {
		padded: false,
		expanded: false
	} );
	this.panel.$element.append( this.pluginPanel.$element );
	mw.hook( 'pdfcreator.mode.add' ).fire( this, this.pluginPanel );
};

ext.pdfcreator.ui.dialog.ExportDialog.prototype.updatePanel = function () {
	this.mode = this.modeDropdown.getSelectedData();
	$( this.pluginPanel.$element ).empty();
	mw.hook( 'pdfcreator.mode.add' ).fire( this, this.pluginPanel );
	if ( this.defaultTemplates[ this.mode ] ) {
		this.templateDropdown.selectByData( this.defaultTemplates[ this.mode ] );
	}
	this.updateSize();
};

ext.pdfcreator.ui.dialog.ExportDialog.prototype.appendTemplates = function () {
	this.templateDropdown = new ext.pdfcreator.ui.TemplateSelector( {
		templates: this.templates,
		$overlay: this.$overlay
	} );
	const templateField = new OO.ui.FieldLayout( this.templateDropdown, {
		label: mw.message( 'pdfcreator-dialog-export-template-selection-label' ).text(),
		align: 'top'
	} );
	this.templateDropdown.selectByData( this.defaultTemplates[ this.mode ] );
	this.panel.$element.append( templateField.$element );
};

ext.pdfcreator.ui.dialog.ExportDialog.prototype.getActionProcess = function ( action ) {
	if ( action ) {
		return new OO.ui.Process( function () {
			this.pushPending();
			const pdfTemplate = this.templateDropdown.getSelectedData();
			const pageId = mw.config.get( 'wgArticleId' );
			const revId = mw.config.get( 'wgRevisionId' );
			const data = {
				mode: this.modeDropdown.getSelectedData(),
				template: pdfTemplate,
				revId: revId
			};

			if ( mw.util.getParamValue( 'redirect' ) ) {
				const redirect = mw.util.getParamValue( 'redirect' );
				data.redirect = redirect;
			}
			mw.hook( 'pdfcreator.export.data' ).fire( this, data );
			const api = new ext.pdfcreator.api.Api();
			api.export( pageId, data ).done( () => {
				this.close( { action: action } );
				this.emit( 'close' );
			} )
				.fail( ( errorMessage ) => {
					this.popPending();
					this.showErrors( new OO.ui.Error( errorMessage, { recoverable: false } ) );
					return;
				} );
		}, this );
	}
	return ext.pdfcreator.ui.dialog.ExportDialog.super.prototype.getActionProcess.call(
		this, action );
};
