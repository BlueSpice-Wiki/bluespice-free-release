$( document ).on( 'click', '.enhanced-filelist-upload-file a', ( e ) => {
	e.preventDefault();
	mw.loader.using( [ 'ext.enhancedUpload.upload.dialog' ] ).done( () => {
		const dialog = new enhancedUpload.ui.dialog.UploadFilesDialog();
		dialog.on( 'uploadcompleted', () => {
			window.location.reload();
		} );
		dialog.show();
	} );
	return false;
} );

mw.hook( 'enhanced.filelist.action' ).add( ( data ) => {
	if ( data.action === 'reupload' ) {
		data.action = '';
		mw.loader.using( [ 'ext.enhancedUpload.singleupload.dialog' ] ).done( () => {
			const dialog = new enhancedUpload.ui.dialog.UploadSingleFileDialog( {
				destFilename: data.row.dbkey,
				dialogTitle: mw.message( 'enhancedupload-single-file-dlg-title', data.row.dbkey ).text()
			} );
			dialog.show();
		} );
	}
} );
