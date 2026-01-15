$( document ).on( 'click', '#ca-upload-file', ( e ) => {
	mw.loader.using( [ 'ext.enhancedUpload.upload.dialog' ] ).done( () => {
		const dialog = new enhancedUpload.ui.dialog.UploadFilesDialog();
		dialog.show();
	} );
	e.defaultPrevented = true;
	return false;
} );
