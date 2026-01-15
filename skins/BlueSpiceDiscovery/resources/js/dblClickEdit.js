/*!
 * Enable double-click-to-edit functionality.
 */
( function ( mw, $ ) {
	if ( Number( mw.user.options.get( 'editondblclick' ) ) !== 1 ) {
		return;
	}

	if ( mw.config.get( 'wgAction' ) !== 'view' ) {
		return;
	}

	$( '#mw-content-text' ).on( 'dblclick', ( e ) => {
		const $visualEdit = $( '#ca-ve-edit' );
		const $sourceEdit = $( '#ca-edit' );

		if ( $visualEdit.length ) {
			e.preventDefault();
			$visualEdit.get( 0 ).click();
		} else if ( $sourceEdit.length ) {
			e.preventDefault();
			$visualEdit.get( 0 ).click();
		}
	} );
}( mediaWiki, jQuery ) );
