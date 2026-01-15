( function ( $ ) {
	$( '.stacked-tabs .nav-tabs a' ).on( 'click', function () {
		const $button = $( this );
		const target = $button.data( 'bsTarget' );

		$button.parents( '.stacked-tabs' ).find( '.tab-pane.show.active' ).removeClass( 'show active' );
		$button.parents( '.stacked-tabs' ).find( '#' + target ).addClass( 'show active' );
	} );
}( jQuery ) );
