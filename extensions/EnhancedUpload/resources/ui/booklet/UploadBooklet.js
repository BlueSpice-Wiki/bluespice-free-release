window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.booklet = window.enhancedUpload.ui.booklet || {};

enhancedUpload.ui.booklet.UploadBooklet = function ( cfg ) {
	enhancedUpload.ui.booklet.UploadBooklet.super.call( this, cfg );
	this.pluginModules = cfg.pluginModules;
	this.makePages();
};

OO.inheritClass( enhancedUpload.ui.booklet.UploadBooklet, OO.ui.BookletLayout );

enhancedUpload.ui.booklet.UploadBooklet.prototype.makePages = function () {
	this.pages = [
		new enhancedUpload.ui.booklet.UploadPage( 'UploadPage', {
			expanded: false,
			pluginModules: this.pluginModules
		} ),
		new enhancedUpload.ui.booklet.ResultPage( 'ResultPage', {
			expanded: false
		} )
	];

	this.pagesOrder = [
		'UploadPage',
		'ResultPage'
	];

	this.addPages( this.pages );
};
