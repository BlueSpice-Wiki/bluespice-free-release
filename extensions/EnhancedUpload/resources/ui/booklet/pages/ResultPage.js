window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.booklet = window.enhancedUpload.ui.booklet || {};

enhancedUpload.ui.booklet.ResultPage = function ( name, cfg ) {
	enhancedUpload.ui.booklet.ResultPage.parent.call( this, name, cfg );
	this.finishedData = [];
	this.failedData = [];
	this.warningData = [];

	this.content = new OO.ui.IndexLayout( {
		expanded: false,
		framed: true,
		classes: [ 'index-width' ]
	} );
	this.contentFailed = new OO.ui.TabPanelLayout( 'failed-upload', {
		label: mw.message( 'enhancedupload-tab-failed-upload' ).text(),
		expanded: false
	} );
	this.contentFinished = new OO.ui.TabPanelLayout( 'finished-upload', {
		label: mw.message( 'enhancedupload-tab-successfull-upload' ).text(),
		expanded: false
	} );
	this.contentWarning = new OO.ui.TabPanelLayout( 'warnings-upload', {
		label: mw.message( 'enhancedupload-tab-warnings-upload' ).text(),
		expanded: false
	} );

	this.content.connect( this, {
		set: function () {
			this.emit( 'dataSet' );
		}
	} );

	this.$element.append( this.content.$element );
};

OO.inheritClass( enhancedUpload.ui.booklet.ResultPage, OO.ui.PageLayout );

enhancedUpload.ui.booklet.ResultPage.prototype.setData = function ( data ) {
	this.finishedData = data.data[ 0 ];
	this.failedData = data.data[ 1 ];
	this.warningData = data.data[ 2 ];
	this.updateUI();
};

enhancedUpload.ui.booklet.ResultPage.prototype.updateUI = function () {
	let renderHeader = false;
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
};

enhancedUpload.ui.booklet.ResultPage.prototype.initializeFailedPanel = function () {
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

enhancedUpload.ui.booklet.ResultPage.prototype.initializeFinishedPanel = function () {
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

enhancedUpload.ui.booklet.ResultPage.prototype.initializeWarningPanel = function () {
	const label = new OO.ui.LabelWidget( {
		label: mw.message( 'enhancedupload-warning-dialog-label', this.warningData.length ).text(),
		classes: [ 'enhancedupload-dialog-title' ]
	} );
	const listView = new enhancedUpload.ui.panel.WarningList( {
		expanded: false
	} );

	listView.addGrid( this.warningData );
	this.contentWarning.$element.append( label.$element );
	this.contentWarning.$element.append( listView.$element );
};
