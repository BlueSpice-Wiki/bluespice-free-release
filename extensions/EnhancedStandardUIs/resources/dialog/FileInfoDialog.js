ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.dialog = ext.enhancedUI.dialog || {};

require( './../booklet/FileInfoBooklet.js' );

ext.enhancedUI.dialog.FileInfoDialog = function ( cfg ) {
	this.fileData = cfg.data;
	this.fileName = cfg.data.dbkey || '';
	this.page = cfg.page || 'preview';
	// Parent constructor
	ext.enhancedUI.dialog.FileInfoDialog.super.apply( this, cfg );

	this.$element.addClass( 'fileinfo-dialog' );
};

OO.inheritClass( ext.enhancedUI.dialog.FileInfoDialog, OO.ui.ProcessDialog );

ext.enhancedUI.dialog.FileInfoDialog.static.name = 'fileinfo-dialog';

ext.enhancedUI.dialog.FileInfoDialog.static.title = mw.message( 'enhanced-standard-uis-filelist-dialog-info' ).text();

ext.enhancedUI.dialog.FileInfoDialog.static.size = 'larger';

ext.enhancedUI.dialog.FileInfoDialog.static.padded = true;

ext.enhancedUI.dialog.FileInfoDialog.static.scrollable = false;

ext.enhancedUI.dialog.FileInfoDialog.static.actions = [
	{
		title: mw.message( 'enhanced-standard-uis-filelist-dialog-action-close-title' ).text(),
		icon: 'close',
		flags: 'safe'
	},
	{
		label: mw.message( 'enhanced-standard-uis-filelist-dialog-action-done-label' ).text(),
		flags: [ 'primary', 'progressive' ]
	},
	{
		label: mw.message( 'enhanced-standard-uis-filelist-dialog-action-file-label' ).text(),
		action: 'file'
	}
];

ext.enhancedUI.dialog.FileInfoDialog.prototype.initialize = function () {
	// Parent method
	ext.enhancedUI.dialog.FileInfoDialog.super.prototype.initialize.apply( this, arguments );

	this.booklet = new ext.enhancedUI.booklet.FileInfoBooklet( {
		expanded: false,
		outlined: true,
		showMenu: false,
		// When auto-focus is enabled - for some reason after changing page is being set twice,
		// which is wrong and breaks stuff.
		// It can be fixed by disabling "autoFocus"
		autoFocus: false,
		fileData: this.fileData
	} );
	this.booklet.connect( this, {
		set: function () {
			this.updateSize();
		},
		update: function () {
			this.updateSize();
		}
	} );

	this.booklet.setPage( this.page );

	this.$body.append( this.booklet.$element );
	this.updateSize();
};

ext.enhancedUI.dialog.FileInfoDialog.prototype.getSetupProcess = function ( data ) {
	data = Object.assign( data, {
		title: this.fileName
	} );
	return ext.enhancedUI.dialog.FileInfoDialog.parent.prototype.getSetupProcess.call( this, data );
};

ext.enhancedUI.dialog.FileInfoDialog.prototype.getActionProcess = function ( action ) {
	return ext.enhancedUI.dialog.FileInfoDialog.parent.prototype.getActionProcess.call(
		this, action
	).next(
		function () {
			if ( action === 'file' ) {
				const filePageUrl = mw.util.getUrl( 'File:' + this.fileName );
				window.location.href = filePageUrl;
			}
			return ext.enhancedUI.dialog.FileInfoDialog.parent.prototype.getActionProcess.call(
				this,
				action
			);
		},
		this
	);
};
