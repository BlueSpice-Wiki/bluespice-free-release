window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.dialog = window.enhancedUpload.ui.dialog || {};

enhancedUpload.ui.dialog.UploadFinishedDialog = function ( cfg ) {
	enhancedUpload.ui.dialog.UploadFinishedDialog.super.call( this, cfg );

	this.finishedData = cfg.data[ 0 ];
	this.failedData = cfg.data[ 1 ];
	this.warningData = cfg.data[ 2 ];
};

OO.inheritClass( enhancedUpload.ui.dialog.UploadFinishedDialog, OO.ui.ProcessDialog );

enhancedUpload.ui.dialog.UploadFinishedDialog.static.name = 'uploadfinished-dialog';
enhancedUpload.ui.dialog.UploadFinishedDialog.static.title = mw.message( 'enhancedupload-finished-dialog-title' ).plain();
enhancedUpload.ui.dialog.UploadFinishedDialog.static.actions = [
	{
		title: mw.message( 'enhancedupload-finished-dialog-action-close' ).plain(),
		icon: 'close',
		flags: 'safe'
	}
];

enhancedUpload.ui.dialog.UploadFinishedDialog.prototype.getSetupProcess = function () {
	return enhancedUpload.ui.dialog.UploadFinishedDialog.super.prototype.getSetupProcess.call(
		this
	).next( function () {
		if ( this.failedData.length ) {
			this.title.setLabel( mw.message( 'enhancedupload-failed-dialog-title' ).plain() );
		}
	}, this );
};

enhancedUpload.ui.dialog.UploadFinishedDialog.prototype.initialize = function () {
	let renderHeader = false;
	enhancedUpload.ui.dialog.UploadFinishedDialog.super.prototype.initialize.call( this );
	this.content = new OO.ui.IndexLayout( {
		expanded: false,
		padded: true,
		framed: true,
		classes: [ 'index-width' ]
	} );

	this.content.connect( this, {
		set: function () {
			this.updateSize();
		}
	} );
	if ( this.failedData.length ) {
		if ( this.finishedData.length || this.warningData.length ) {
			renderHeader = true;
		}
		this.initializeFailedPanel();
		this.content.addTabPanels( [ this.contentFailed ] );
	}

	if ( this.finishedData.length ) {
		if ( !renderHeader && this.warningData.length ) {
			renderHeader = true;
		}
		this.initializeFinishedPanel();
		this.content.addTabPanels( [ this.contentFinished ] );
	}

	if ( this.warningData.length ) {
		this.initializeWarningPanel();
		this.content.addTabPanels( [ this.contentWarning ] );
	}

	if ( !renderHeader ) {
		this.content.getTabs().items[ 0 ].$element.hide();
	}

	this.$body.append( this.content.$element );
	this.updateSize();
};

enhancedUpload.ui.dialog.UploadFinishedDialog.prototype.show = function () {
	if ( !this.windowManager ) {
		this.windowManager = new OO.ui.WindowManager( {
			modal: true
		} );
		$( document.body ).append( this.windowManager.$element );
		this.windowManager.addWindows( [ this ] );
		this.windowManager.connect( this, {
			closing: function () {
				this.emit( 'close' );
			}
		} );
	}
	this.windowManager.openWindow( this );
};

enhancedUpload.ui.dialog.UploadFinishedDialog.prototype.initializeFailedPanel = function () {
	this.contentFailed = new OO.ui.TabPanelLayout( 'failed-upload', {
		label: mw.message( 'enhancedupload-tab-failed-upload' ).text(),
		expanded: false
	} );
	const label = new OO.ui.LabelWidget( {
		label: mw.message( 'enhancedupload-failed-dialog-label', this.failedData.length ).text(),
		classes: [ 'enhancedupload-dialog-title' ]
	} );
	const listView = new enhancedUpload.ui.panel.FailedList( {
		expanded: false
	} );
	listView.addGrid( this.failedData );
	this.contentFailed.$element.append( label.$element );
	this.contentFailed.$element.append( listView.$element );
};

enhancedUpload.ui.dialog.UploadFinishedDialog.prototype.initializeFinishedPanel = function () {
	this.contentFinished = new OO.ui.TabPanelLayout( 'finished-upload', {
		label: mw.message( 'enhancedupload-tab-successfull-upload' ).text(),
		expanded: false
	} );
	const label = new OO.ui.LabelWidget( {
		label: mw.message( 'enhancedupload-finished-dialog-label' ).text(),
		classes: [ 'enhancedupload-dialog-title' ]
	} );
	const listView = new enhancedUpload.ui.panel.FinishedList( {
		expanded: false
	} );
	listView.addGrid( this.finishedData );
	this.contentFinished.$element.append( label.$element );
	this.contentFinished.$element.append( listView.$element );
};

enhancedUpload.ui.dialog.UploadFinishedDialog.prototype.initializeWarningPanel = function () {
	this.contentWarning = new OO.ui.TabPanelLayout( 'warnings-upload', {
		label: mw.message( 'enhancedupload-tab-warnings-upload' ).text(),
		expanded: false
	} );
	const label = new OO.ui.LabelWidget( {
		label: mw.message( 'enhancedupload-warning-dialog-label', this.warningData.length ).text(),
		classes: [ 'enhancedupload-dialog-title' ]
	} );
	const listView = new enhancedUpload.ui.panel.WarningList( {
		expanded: false,
		padded: true
	} );

	listView.connect( this, {
		dataset: () => {
			this.updateSize();
		}
	} );

	listView.addGrid( this.warningData );
	this.contentWarning.$element.append( label.$element );
	this.contentWarning.$element.append( listView.$element );
};
