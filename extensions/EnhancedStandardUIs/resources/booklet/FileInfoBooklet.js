ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.booklet = ext.enhancedUI.booklet || {};

require( './pages/DataPage.js' );
require( './pages/HistoryPage.js' );
require( './pages/PreviewPage.js' );

ext.enhancedUI.booklet.FileInfoBooklet = function ( cfg ) {
	this.fileData = cfg.fileData;
	ext.enhancedUI.booklet.FileInfoBooklet.super.call( this, cfg );
	this.makePages();
};

OO.inheritClass( ext.enhancedUI.booklet.FileInfoBooklet, OO.ui.BookletLayout );

ext.enhancedUI.booklet.FileInfoBooklet.prototype.makePages = function () {
	this.pages = [
		new ext.enhancedUI.booklet.PreviewPage( 'Preview', {
			expanded: false,
			padded: true,
			fileData: this.fileData
		} ),
		new ext.enhancedUI.booklet.DataPage( 'Data', {
			expanded: false,
			padded: true,
			fileData: this.fileData
		} ),
		new ext.enhancedUI.booklet.HistoryPage( 'History', {
			expanded: false,
			padded: true,
			fileData: this.fileData
		} )
	];

	for ( const page in this.pages ) {
		this.pages[ page ].connect( this, {
			update: function () {
				this.emit( 'update' );
			}
		} );
	}

	this.pagesOrder = [
		'Preview',
		'Data',
		'History'
	];

	this.addPages( this.pages );
};
