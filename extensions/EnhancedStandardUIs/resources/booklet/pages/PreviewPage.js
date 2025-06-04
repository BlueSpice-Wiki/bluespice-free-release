ext.enhancedUI.booklet.PreviewPage = function ( name, cfg ) {
	cfg = cfg || {};
	this.fileData = cfg.fileData;
	this.fileName = cfg.fileData.dbkey;
	this.fileUrl = cfg.fileData.fileUrl;
	this.fileWidth = cfg.width || 300;
	this.fileHeight = cfg.height || 'auto';
	ext.enhancedUI.booklet.PreviewPage.parent.call( this, name, cfg );

	const mimeType = this.fileData.img_mime_major;
	if ( mimeType !== 'image' ) {
		this.fileUrl = this.fileData.preview_url;
		this.fileWidth = 120;
		this.fileHeight = 'auto';
	}

	const $image = $( '<img>' ).addClass( 'file-info-image' )
		.attr( 'alt', this.fileName )
		.attr( 'src', this.fileUrl )
		.attr( 'width', this.fileWidth )
		.attr( 'height', this.fileHeight );
	this.$element.append( $image );
};

OO.inheritClass( ext.enhancedUI.booklet.PreviewPage, OO.ui.PageLayout );

ext.enhancedUI.booklet.PreviewPage.prototype.setupOutlineItem = function () {
	this.outlineItem.setLabel( mw.message( 'enhanced-standard-uis-filelist-dialog-page-preview-label' ).text() );
};
