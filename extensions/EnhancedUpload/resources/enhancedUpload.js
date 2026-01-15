$( () => {
	let singleUpload = false;
	let destFilename = '';
	if ( mw.util.getParamValue( 'wpDestFile' ) ) {
		singleUpload = true;
		destFilename = mw.util.getParamValue( 'wpDestFile' );
	}

	// eslint-disable-next-line no-jquery/no-global-selector
	$( '#enhancedUpload-container' ).append(
		new enhancedUpload.ui.UploadWidget( {
			singleUpload: singleUpload,
			destFilename: destFilename
		} ).$element
	);
	if ( $( document ).find( '#enhancedUpload-skeleton-cnt' ) ) {
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '#enhancedUpload-skeleton-cnt' ).empty();
	}

	// eslint-disable-next-line no-jquery/no-global-selector
	$( '#enhancedUpload-container' ).addClass( 'enhancedUpload-widget' );
} );
