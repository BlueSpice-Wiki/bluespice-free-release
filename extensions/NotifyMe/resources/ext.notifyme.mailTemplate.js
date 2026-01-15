$( () => {
	const $iframe = $( '#mail-template' );
	if ( !$iframe.length ) {
		return;
	}
	const frame = $iframe[ 0 ];

	frame.contentWindow.document.open( 'text/html', 'replace' );
	frame.contentWindow.document.write( $iframe.data( 'html' ) );
	frame.contentWindow.document.close();
} );
