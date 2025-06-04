'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.dialog = ext.pdfcreator.ui.dialog || {};

ext.pdfcreator.ui.dialog.DeleteDialog = function ( cfg ) {
	cfg = cfg || {};
	cfg.classes = [ 'pdfcreator-delete-dlg' ];
	ext.pdfcreator.ui.dialog.DeleteDialog.parent.call( this, cfg );
	this.template = cfg.template || {};
};

OO.inheritClass( ext.pdfcreator.ui.dialog.DeleteDialog, OO.ui.ProcessDialog );

ext.pdfcreator.ui.dialog.DeleteDialog.static.name = 'PDFDeleteDialog';
ext.pdfcreator.ui.dialog.DeleteDialog.static.title = mw.message( 'pdfcreator-template-delete-dlg-title' ).text();
ext.pdfcreator.ui.dialog.DeleteDialog.static.size = 'medium';
ext.pdfcreator.ui.dialog.DeleteDialog.static.actions = [
	{
		action: 'delete',
		label: mw.message( 'pdfcreator-template-delete-dlg-action-delete' ).text(),
		flags: [ 'primary', 'destructive' ]
	},
	{
		title: mw.message( 'cancel' ).text(),
		flags: [ 'safe', 'close' ]
	}
];

ext.pdfcreator.ui.dialog.DeleteDialog.prototype.initialize = function () {
	ext.pdfcreator.ui.dialog.DeleteDialog.super.prototype.initialize.apply( this, arguments );
	this.panel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	const label = new OO.ui.LabelWidget( {
		label: mw.message( 'pdfcreator-template-delete-dlg-confirm-text', this.template ).text(),
		padded: true,
		expanded: true
	} );

	this.panel.$element.append( label.$element );

	this.$body.append( this.panel.$element );
	this.updateSize();
};

ext.pdfcreator.ui.dialog.DeleteDialog.prototype.getActionProcess = function ( action ) {
	return ext.pdfcreator.ui.dialog.DeleteDialog.parent.prototype.getActionProcess.call(
		this, action )
		.next(
			function () {
				const dfd = $.Deferred();
				if ( action === 'delete' ) {
					this.delete().done( () => {
						dfd.resolve();
						this.close();
						this.emit( 'deleted' );
					} ).fail( ( error ) => {
						this.showErrors( error );
						dfd.reject();
					} );
					return dfd.promise();
				}

				return ext.pdfcreator.ui.dialog.DeleteDialog.parent.prototype.getActionProcess.call(
					this,
					action
				);
			},
			this
		);
};

ext.pdfcreator.ui.dialog.DeleteDialog.prototype.delete = function () {
	const dfd = $.Deferred();

	this.pushPending();
	mw.loader.using( [ 'mediawiki.api' ] ).done( () => {
		const mwApi = new mw.Api();
		const params = {
			action: 'delete',
			title: 'MediaWiki:PDFCreator/' + this.template,
			format: 'json'
		};
		mwApi.postWithToken( 'csrf', params ).done( () => {
			dfd.resolve();
		} ).fail( ( error ) => {
			dfd.reject( new OO.ui.Error( error, { recoverable: false } ) );
		} );
	} );

	return dfd.promise();
};
