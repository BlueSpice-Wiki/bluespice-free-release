window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.dialog = window.enhancedUpload.ui.dialog || {};

enhancedUpload.ui.dialog.UploadFilesDialog = function ( cfg ) {
	enhancedUpload.ui.dialog.UploadFilesDialog.super.call( this, cfg );
};

OO.inheritClass( enhancedUpload.ui.dialog.UploadFilesDialog, OO.ui.ProcessDialog );

enhancedUpload.ui.dialog.UploadFilesDialog.static.name = 'upload-media-dialog';
enhancedUpload.ui.dialog.UploadFilesDialog.static.title =
	mw.message( 'enhancedupload-upload-media-dialog-title' ).plain();
enhancedUpload.ui.dialog.UploadFilesDialog.static.size = 'large';

enhancedUpload.ui.dialog.UploadFilesDialog.static.actions = [
	{
		title: mw.message( 'enhancedupload-upload-media-dialog-cancel-title' ).plain(),
		icon: 'close',
		flags: 'safe',
		modes: [ 'UploadPage' ]
	},
	{
		label: mw.message( 'enhancedupload-upload-media-dialog-upload-title' ).plain(),
		action: 'add',
		flags: [ 'primary', 'progressive' ],
		modes: [ 'UploadPage' ]
	},
	{
		action: 'done',
		label: mw.message( 'enhancedupload-upload-media-dialog-done-title' ).text(),
		flags: [ 'primary', 'progressive' ],
		modes: [ 'ResultPage' ]
	}
];

enhancedUpload.ui.dialog.UploadFilesDialog.prototype.getSetupProcess = function () {
	return enhancedUpload.ui.dialog.UploadFilesDialog.parent.prototype.getSetupProcess.call( this )
		.next( function () {
			// Prevent flickering, disable all actions before init is done
			this.actions.setMode( 'INVALID' );
		}, this );
};

enhancedUpload.ui.dialog.UploadFilesDialog.prototype.initialize = function () {
	const pluginModules = require( './pluginModules.json' );
	enhancedUpload.ui.dialog.UploadFilesDialog.super.prototype.initialize.call( this );
	this.booklet = new enhancedUpload.ui.booklet.UploadBooklet( {
		expanded: false,
		outlined: false,
		showMenu: false,
		// When auto-focus is enabled -
		// for some reason after changing page is being set twice (which is wrong and
		// breaks stuff.
		// It can be fixed by disabling "autoFocus"
		autoFocus: false,
		pluginModules: pluginModules,
		classes: [ 'upload-dialog' ]
	} );

	this.$body.append( this.booklet.$element );
};

enhancedUpload.ui.dialog.UploadFilesDialog.prototype.show = function () {
	if ( !this.windowManager ) {
		this.windowManager = new OO.ui.WindowManager( {
			modal: true
		} );
		$( document.body ).append( this.windowManager.$element );
		this.windowManager.addWindows( [ this ] );
	}
	this.windowManager.openWindow( this );
};

enhancedUpload.ui.dialog.UploadFilesDialog.prototype.switchPage = function ( name, data ) {
	const page = this.booklet.getPage( name );
	if ( !page ) {
		return;
	}

	this.booklet.setPage( name );
	this.actions.setMode( name );
	this.popPending();

	switch ( name ) {
		case 'UploadPage':
			page.connect( this, {
				filesUpdated: function () {
					this.actions.setAbilities( { add: true } );
					this.updateSize();
				},
				uploadData: function ( files ) {
					this.pushPending();
					this.switchPage( 'ResultPage', { data: files } );
				},
				clearedPreview: function () {
					this.actions.setAbilities( { add: false } );
					this.updateSize();
				}
			} );
			this.updateSize();
			break;
		case 'ResultPage':
			this.actions.setAbilities( { cancel: false, add: false, done: true } );
			page.connect( this, {
				dataSet: function () {
					this.updateSize();
				}
			} );
			page.setData( data );
			this.updateSize();
			break;
	}
};

enhancedUpload.ui.dialog.UploadFilesDialog.prototype.getReadyProcess = function ( data ) {
	return enhancedUpload.ui.dialog.UploadFilesDialog.parent.prototype.getReadyProcess.call(
		this, data
	).next(
		function () {
			this.switchPage( 'UploadPage' );
			this.actions.setAbilities( { cancel: true, add: false, done: false } );
		},
		this
	);
};

enhancedUpload.ui.dialog.UploadFilesDialog.prototype.getActionProcess = function ( action ) {
	const page = this.booklet.getCurrentPage();
	return enhancedUpload.ui.dialog.UploadFilesDialog.parent.prototype.getActionProcess.call(
		this, action
	).next(
		function () {
			switch ( action ) {
				case 'add':
					page.upload.startUpload();
					break;
				case 'done':
					this.emit( 'uploadcompleted' );
					this.close();
					break;
			}
		},
		this
	);
};

enhancedUpload.ui.dialog.UploadFilesDialog.prototype.getBodyHeight = function () {
	return this.$element.find( '.oo-ui-window-body' )[ 0 ].scrollHeight + 85;
};
