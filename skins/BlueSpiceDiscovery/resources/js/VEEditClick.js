( function ( $ ) {
	// With primary-ca-ve-edit ID we have second ID for visual editor
	// but should be same behavior like clicking on ca-ve-edit and loading
	// VisualEditor without reloading the page
	$( '#primary-ca-ve-edit' ).on( 'click', ( e ) => {
		const $visualEdit = $( '#ca-ve-edit' );
		if ( $visualEdit.length ) {
			e.preventDefault();
			$visualEdit.get( 0 ).click();
		}

	} );
}( jQuery ) );
