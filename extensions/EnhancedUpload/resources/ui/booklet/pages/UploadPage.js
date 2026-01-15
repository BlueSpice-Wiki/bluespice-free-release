window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.booklet = window.enhancedUpload.ui.booklet || {};

enhancedUpload.ui.booklet.UploadPage = function ( name, cfg ) {
	enhancedUpload.ui.booklet.UploadPage.parent.call( this, name, cfg );
	this.pluginModules = cfg.pluginModules;

	this.content = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true,
		classes: [ 'enhancedUpload-widget' ]
	} );

	this.upload = new enhancedUpload.ui.UploadWidget( {
		pluginModules: this.pluginModules,
		container: this.content.$element,
		hideFinishedDialog: true,
		emitUploadData: true
	} );

	this.upload.connect( this, {
		filesUpdated: function () {
			this.emit( 'filesUpdated' );
		},
		uploadData: function ( data ) {
			this.emit( 'uploadData', data );
		},
		clearPreview: function () {
			this.emit( 'clearedPreview' );
		}
	} );

	this.$element.append( this.content.$element );
};

OO.inheritClass( enhancedUpload.ui.booklet.UploadPage, OO.ui.PageLayout );
