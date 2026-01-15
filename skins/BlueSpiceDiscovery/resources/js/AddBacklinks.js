( function ( mw, $, d ) {

	function buildURL( url ) {
		const parsed = new URL( url, mw.config.get( 'wgServer' ) );
		if ( parsed.hostname !== window.location.hostname ) {
			return url;
		}
		if ( parsed.searchParams.has( 'backTo' ) ) {
			return url;
		}
		parsed.searchParams.set( 'backTo', mw.config.get( 'wgPageName' ) );
		return parsed.toString();
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
