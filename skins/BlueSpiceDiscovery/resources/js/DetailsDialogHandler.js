( function ( mw, $, d ) {
	$( d ).on( 'click', '#ca-details', function ( e ) {
		e.preventDefault();
		const links = $( this ).data( 'toolbox' );
		mw.loader.using( 'skin.discovery.dialog.details' ).done( () => {
			const windowManager = OO.ui.getWindowManager();
			const dialog = new window.bs.skin.dialog.DetailsDialog( {
				links: links
			} );
			windowManager.addWindows( [ dialog ] );
			windowManager.openWindow( dialog );
		} );
		return false;
	} );
}( mediaWiki, jQuery, document ) );
