( function ( mw, $, d ) {

	function toggleClass( $el ) {
		const classes = ( $el.attr( 'class' ) || '' ).split( ' ' );
		const iconClass = classes.find( ( c ) => /^bi-[a-z0-9-]+$/.test( c ) || /^bi-[a-z0-9-]+-fill$/.test( c ) );
		if ( !iconClass ) {
			return;
		}
		let newClass;
		if ( iconClass.endsWith( '-fill' ) ) {
			newClass = iconClass.replace( /-fill$/, '' );
		} else {
			newClass = iconClass + '-fill';
		}
		$el.removeClass( iconClass ).addClass( newClass ); // eslint-disable-line mediawiki/class-doc
	}

	$( d ).on( 'click', '#ca-watch, #ca-unwatch', function ( e ) {
		const $this = $( this );
		const currentPage = mw.config.get( 'wgPageName' );
		mw.loader.using( 'mediawiki.api' ).done( () => {
			const api = new mw.Api();
			if ( $this.attr( 'id' ) === 'ca-watch' ) {
				api.watch( currentPage ).done( () => {
					$this.attr( 'id', 'ca-unwatch' );
					$this.attr( 'aria-label', mw.message( 'unwatch' ).plain() );
					$this.attr( 'title', mw.message( 'tooltip-ca-unwatch' ).plain() );
					toggleClass( $this );
					mw.notify( mw.message( 'addedwatchtext-short', currentPage ).text() );
				} );
				e.preventDefault();
				return false;
			}
			api.unwatch( currentPage ).done( () => {
				$this.attr( 'id', 'ca-watch' );
				$this.attr( 'aria-label', mw.message( 'watch' ).plain() );
				$this.attr( 'title', mw.message( 'tooltip-ca-watch' ).plain() );
				toggleClass( $this );
				mw.notify( mw.message( 'removedwatchtext-short', currentPage ).text() );
			} );
		} );
		e.preventDefault();
		return false;
	} );
}( mediaWiki, jQuery, document ) );
