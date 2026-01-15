( function ( mw, $, d ) {

	function buildURL( url ) {
		const backTo = 'backTo=' + mw.util.rawurlencode( mw.util.rawurlencode( mw.config.get( 'wgPageName' ) ) );
		let connector = '?';
		if ( url.indexOf( '?' ) > 0 ) {
			connector = '&';
		}
		return url + connector + backTo;
	}

	$( d ).on( 'click', '.ns-special #content a', function ( e ) {
		if ( e.currentTarget.title === mw.config.get( 'wgPageName' ) ) {
			return;
		}
		const href = e.currentTarget.href;
		if ( href.length === 0 ) {
			return;
		}
		if ( href.includes( 'returnto' ) ) {
			return;
		}
		if ( href.includes( mw.config.get( 'wgPageName' ) ) ) {
			return;
		}

		this.href = buildURL( href );
	} );

	$( d ).on( 'click', '#content a.new', function ( e ) {
		const href = e.currentTarget.href;
		if ( href.length === 0 ) {
			return;
		}
		if ( href.includes( 'returnto' ) ) {
			return;
		}
		if ( href.includes( mw.config.get( 'wgPageName' ) ) ) {
			return;
		}
		this.href = buildURL( href );
	} );

}( mediaWiki, jQuery, document ) );
