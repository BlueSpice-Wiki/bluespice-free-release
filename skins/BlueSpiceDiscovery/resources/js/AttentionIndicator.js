$( () => {
	const indicators = {};
	const keys = [];
	$( '[data-attentionindicator]' ).each( function () {
		indicators[ $( this ).data( 'attentionindicator' ) ] = $( this );
		keys.push( $( this ).data( 'attentionindicator' ) );
	} );
	if ( keys.length < 1 ) {
		return;
	}

	const callback = async () => {
		const url =
			mw.util.wikiScript( 'rest' ) +
			'/bluespice/discovery/attention-indicators?indicators=' + encodeURIComponent( keys.join( ',' ) );

		const res = await fetch( url );
		if ( !res.ok ) {
			console.warn( 'AttentionIndicator: Failed to fetch indicator data' ); // eslint-disable-line no-console
			return;
		}
		const result = await res.json();
		if ( Object.keys( result ).length < 1 ) {
			return;
		}

		let indication = false;
		let msg = false;
		let $children = [];
		for ( const i in result ) {
			if ( !indicators[ i ] ) {
				continue;
			}
			if ( result[ i ] ) {
				if ( !indicators[ i ].hasClass( 'attention-indicator' ) ) {
					msg = mw.message( 'bs-discovery-requires-attention' );
					const $span = $( '<span>' );
					$span.addClass( 'visually-hidden' );
					$span.html( msg.escaped() );
					indicators[ i ].append( $span );
				}
				indicators[ i ].addClass( 'attention-indicator' );
				indication = true;
				continue;
			}
			if ( indicators[ i ].hasClass( 'attention-indicator' ) ) {
				$children = indicators[ i ].children( '.visually-hidden' );
				if ( $children.length > 0 ) {
					$children.first().remove();
				}
			}
			indicators[ i ].removeClass( 'attention-indicator' );
		}
		if ( indication ) {
			$( '#usr-btn' ).addClass( 'attention-indicator' );
			msg = mw.message( 'bs-discovery-navbar-user-button-requires-attention-aria-label' );
			$( '#usr-btn' ).attr( 'aria-label', msg.escaped() );
			$( '#usr-btn > i' ).parent().addClass( 'attention-indicator-icon' );
			return;
		}
		$( '#usr-btn' ).removeClass( 'attention-indicator' );
		msg = mw.message( 'bs-discovery-navbar-user-button-aria-label' );
		$( '#usr-btn' ).attr( 'aria-label', msg.escaped() );
		$( '#usr-btn > i' ).parent().removeClass( 'attention-indicator-icon' );
	};
	callback();
} );
