ext.enhancedUI.booklet.DataPage = function ( name, cfg ) {
	this.fileName = cfg.fileData.dbkey;
	ext.enhancedUI.booklet.DataPage.parent.call( this, name, cfg );
	this.versions = [];
	this.getElements().done( () => {
		if ( this.versions.length === 0 ) {
			const label = new OO.ui.LabelWidget( {
				label: mw.message( 'enhanced-standard-uis-filelist-dialog-page-data-no-metadata-label' ).text()
			} );
			this.$element.append( label.$element );
			this.emit( 'update' );
		}
		const $table = $( '<ul>' ).addClass( 'wikitable' );
		for ( const key in this.versions ) {
			const $tr = $( '<tr>' );
			const $tdName = $( '<td>' ).text( this.versions[ key ].name );
			const $tdValue = $( '<td>' ).text( this.versions[ key ].value );
			$tr.append( $tdName ).append( $tdValue );
			$table.append( $tr );
			this.$element.append( $table );
			this.emit( 'update' );
		}
	} );
};

OO.inheritClass( ext.enhancedUI.booklet.DataPage, OO.ui.PageLayout );

ext.enhancedUI.booklet.DataPage.prototype.setupOutlineItem = function () {
	this.outlineItem.setLabel( mw.message( 'enhanced-standard-uis-filelist-dialog-page-data-label' ).text() );
};

ext.enhancedUI.booklet.DataPage.prototype.getElements = function () {
	const dfd = $.Deferred();
	mw.loader.using( [ 'ext.enhancedstandarduis.api' ], () => {
		const metadataApi = new ext.enhancedUI.api.Api();
		metadataApi.getFileMetadata( this.fileName ).done( ( data ) => {
			this.versions = data;
			dfd.resolve();
		} ).fail( () => {
			dfd.resolve();
		} );
	} );

	return dfd.promise();
};
