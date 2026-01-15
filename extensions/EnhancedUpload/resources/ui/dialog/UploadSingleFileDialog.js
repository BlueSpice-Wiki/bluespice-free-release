window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.dialog = window.enhancedUpload.ui.dialog || {};

enhancedUpload.ui.dialog.UploadSingleFileDialog = function ( cfg ) {
	this.destFilename = cfg.destFilename;
	this.dialogTitle = cfg.dialogTitle || this.static.title;
	enhancedUpload.ui.dialog.UploadSingleFileDialog.super.call( this, cfg );
};

OO.inheritClass( enhancedUpload.ui.dialog.UploadSingleFileDialog, OO.ui.ProcessDialog );

enhancedUpload.ui.dialog.UploadSingleFileDialog.static.name = 'upload-media-dialog';
enhancedUpload.ui.dialog.UploadSingleFileDialog.static.title =
	mw.message( 'enhancedupload-upload-media-dialog-title' ).plain();
enhancedUpload.ui.dialog.UploadSingleFileDialog.static.size = 'medium';

enhancedUpload.ui.dialog.UploadSingleFileDialog.static.actions = [
	{
		title: mw.message( 'enhancedupload-upload-media-dialog-cancel-title' ).plain(),
		icon: 'close',
		flags: 'safe'
	},
	{
		label: mw.message( 'enhancedupload-upload-media-dialog-upload-title' ).plain(),
		action: 'done',
		flags: [ 'primary', 'progressive' ]
	}
];

enhancedUpload.ui.dialog.UploadSingleFileDialog.prototype.getSetupProcess = function ( data ) {
	data = Object.assign( data, {
		title: this.dialogTitle
	} );
	return enhancedUpload.ui.dialog.UploadSingleFileDialog.parent.prototype.getSetupProcess.call(
		this, data );
};

enhancedUpload.ui.dialog.UploadSingleFileDialog.prototype.initialize = function () {
	const pluginModules = require( './pluginModules.json' );
	enhancedUpload.ui.dialog.UploadSingleFileDialog.super.prototype.initialize.call( this );

	this.content = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true,
		classes: [ 'enhancedUpload-widget' ]
	} );

	this.upload = new enhancedUpload.ui.UploadWidget( {
		pluginModules: pluginModules,
		container: this.content.$element,
		expandedOptions: false,
		singleUpload: true,
		destFilename: this.destFilename,
		hideFinishedDialog: true,
		emitUploadData: true
	} );
	this.upload.connect( this, {
		uploadData: function ( files ) {
			if ( files[ 0 ].length > 0 || files[ 1 ].length === 0 && files[ 2 ].length === 0 ) {
				this.close();
			} else {
				// TODO error handling if something went wrong
				const error = this.findError( files );
				this.showErrors( error );
			}
		},
		toggled: function () {
			this.updateSize();
		}
	} );
	this.upload.selectFiles.connect( this, {
		change: function () {
			this.actions.setAbilities( { done: true } );
		}
	} );

	this.$body.append( this.content.$element );
};

enhancedUpload.ui.dialog.UploadSingleFileDialog.prototype.show = function () {
	if ( !this.windowManager ) {
		this.windowManager = new OO.ui.WindowManager( {
			modal: true
		} );
		$( document.body ).append( this.windowManager.$element );
		this.windowManager.addWindows( [ this ] );
	}
	this.windowManager.openWindow( this );
};

enhancedUpload.ui.dialog.UploadSingleFileDialog.prototype.getReadyProcess = function ( data ) {
	return enhancedUpload.ui.dialog.UploadSingleFileDialog.parent.prototype.getReadyProcess.call(
		this, data
	).next(
		function () {
			this.actions.setAbilities( { cancel: true, done: false } );
		},
		this
	);
};

enhancedUpload.ui.dialog.UploadSingleFileDialog.prototype.getActionProcess = function ( action ) {
	return enhancedUpload.ui.dialog.UploadSingleFileDialog.parent.prototype.getActionProcess.call(
		this, action
	).next(
		function () {
			if ( action === 'done' ) {
				this.upload.startUpload();
			}
		},
		this
	);
};

enhancedUpload.ui.dialog.UploadSingleFileDialog.prototype.getBodyHeight = function () {
	return this.$element.find( '.oo-ui-window-body' )[ 0 ].scrollHeight;
};

enhancedUpload.ui.dialog.UploadSingleFileDialog.prototype.findError = function ( files ) {
	// failed upload
	if ( files[ 1 ].length > 0 ) {
		return new OO.ui.Error( files[ 1 ][ 0 ][ 0 ], { recoverable: false } );
	}
	// warning during upload
	if ( files[ 2 ].length > 0 ) {
		return new OO.ui.Error( files[ 2 ][ 0 ][ 0 ], { recoverable: false } );
	}
	// catch error if something completely different occured
	return new OO.ui.Error( mw.message( 'uploaderror' ).text(), { recoverable: false } );
};
