( function ( $, d ) {
	scrollToTop = { // eslint-disable-line no-implicit-globals, no-undef
		duration: 400,
		offset: 300
	};

	$( window ).on( 'scroll', function () {
		if ( $( this ).scrollTop() > scrollToTop.offset ) { // eslint-disable-line no-undef
			$( '#back-to-top' ).removeClass( 'd-none' );
		} else {
			$( '#back-to-top' ).addClass( 'd-none' );
		}
	} );

	$( d ).on( 'click', '.back-to-top', ( e ) => {
		e.preventDefault();
		$( 'body,html' ).animate(
			{
				scrollTop: 0
			},
			scrollToTop.duration // eslint-disable-line no-undef
		);

		$( '#content' ).first().trigger( 'focus' );
	} );

}( jQuery, document ) );
