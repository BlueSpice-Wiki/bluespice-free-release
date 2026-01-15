( function ( mw, $, d ) {
	$( d ).on( 'click', '#t-permalink', function ( e ) {
		e.preventDefault();
		const $temp = $( '<input>' );
		$( 'body' ).append( $temp );
		$temp.val( mw.config.get( 'wgServer', '' ) + $( this ).attr( 'href' ) ).trigger( 'select' );
		d.execCommand( 'copy' );
		$temp.remove();
		mw.notify( mw.msg( 'mw-widgets-copytextlayout-copy-success' ) );
		return false;
	} );
}( mediaWiki, jQuery, document ) );
