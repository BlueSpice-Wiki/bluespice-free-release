( ( $ ) => {

	$( () => {
		const banner = new ext.bluespice.about.ui.BannerRating();
		const $wrapper = $( '<div>' ).attr( 'id', 'bs-about-banner-rating-container' );
		$wrapper.append( banner.$element );
		$( 'body' ).append( $wrapper );

		if ( $( document ).find( '#bs-about-banner-newsletter-container' ) ) {
			$( '#bs-about-banner-newsletter-container' ).css( 'bottom', $wrapper.innerHeight() );
			banner.connect( this, {
				removedRating: () => {
					$( '#bs-about-banner-newsletter-container' ).css( 'bottom', 0 );
				}
			} );
		}
	} );

} )( jQuery );
