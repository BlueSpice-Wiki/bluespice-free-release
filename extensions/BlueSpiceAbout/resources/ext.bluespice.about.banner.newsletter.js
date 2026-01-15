( ( $ ) => {

	$( () => {
		const banner = new ext.bluespice.about.ui.BannerNewsletter();
		const $wrapper = $( '<div>' ).attr( 'id', 'bs-about-banner-newsletter-container' );
		$wrapper.append( banner.$element );
		$( 'body' ).append( $wrapper );
	} );

} )( jQuery );
