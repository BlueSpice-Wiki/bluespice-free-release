( function ( mw, $ ) {
	const interval = mw.config.get( 'bsgWhoIsOnlineInterval', 0 ) * 1000;
	if ( interval < 1 ) {
		return;
	}
	if ( !String.prototype.startsWith ) {
		// old browsers
		Object.defineProperty( String.prototype, 'startsWith', { // eslint-disable-line no-extend-native
			value: function ( search, rawPos ) {
				const pos = rawPos > 0 ? rawPos | 0 : 0; // eslint-disable-line no-bitwise
				return this.substring( pos, pos + search.length ) === search; // eslint-disable-line unicorn/prefer-string-slice
			}
		} );
	}
	let markers = {};
	const onlineMarkers = function () {
		$( '.bs-userminiprofile[data-bs-whoisonline-marker]' ).each( function () {
			const userName = $( this ).data( 'bs-whoisonline-marker' );
			if ( !userName || userName.length < 1 ) {
				return;
			}
			markers[ userName ] = 'unchecked';
		} );
		return markers;
	};
	const listener = function ( result, Listener ) { // eslint-disable-line no-unused-vars
		if ( result.success !== true ) {
			return;
		}

		markers = result.onlinemarkers;
		$( '.bs-userminiprofile[data-bs-whoisonline-marker]' ).removeClass( ( index, classString ) => { // eslint-disable-line mediawiki/class-doc
			const classes = classString.split( ' ' ),
				remove = [];
			for ( let i = 0; classes.length > i; i++ ) {
				if ( classes[ i ].startsWith( 'bs-whoisonline-marker-' ) !== true ) {
					continue;
				}
				remove.push( classes[ i ] );
			}
			return remove.join( ' ' );
		} );

		$( '.bs-userminiprofile[data-bs-whoisonline-marker]' ).each( function () {
			const userName = $( this ).data( 'bs-whoisonline-marker' );
			if ( !userName || userName.length < 1 ) {
				return;
			}
			const userMarker = ( markers[ userName ] ? markers[ userName ] : 'unchecked' );
			$( this ).addClass( 'bs-whoisonline-marker-' + userMarker ); // eslint-disable-line mediawiki/class-doc
			if ( userMarker !== 'unchecked' ) {
				$( this ).find( 'a' ).attr( 'aria-label', userName + ' ' + userMarker );
				$( this ).find( 'div.bs-social-entity-profileimage-wrapper' ).attr( 'aria-label', userName + ' ' + userMarker );
			}
		} );

		$( '.bs-whoisonline-portlet' ).each( function () {
			const portlet = result.portletItems;
			$( this ).html( portlet );
		} );

		$( '.bs-whoisonline-count' ).each( function () {
			$( this ).html( result.count );
		} );

		BSPing.registerListener(
			'WhoIsOnline',
			interval,
			[ { onlinemarkers: onlineMarkers() } ],
			listener
		);
	};
	BSPing.registerListener(
		'WhoIsOnline',
		interval,
		[ { onlinemarkers: onlineMarkers() } ],
		listener
	);

	let whoIsOnlinePopup = null;

	function getPopupContent( data ) {
		const $panel = $( '<div>' );
		if ( data.length > 0 ) {
			data = data.split( ',' );
			for ( const key in data ) {
				const userWidget = new OOJSPlus.ui.widget.UserWidget( {
					user_name: data[ key ], // eslint-disable-line camelcase
					showLink: true,
					showRawUsername: false
				} );
				$panel.append( userWidget.$element );
			}
		} else {
			const $span = $( '<span>' ).text( mw.message( 'bs-whoisonline-nousers' ).text() );
			$panel.append( $span );
		}
		return $panel;
	}

	function showPopup( element ) {
		const targetId = '#' + $( element ).attr( 'data-target-id' );
		const targetData = $( element ).attr( 'data-target' );
		if ( !whoIsOnlinePopup ) {
			const content = getPopupContent( targetData );
			whoIsOnlinePopup = new OO.ui.PopupWidget( {
				$content: content,
				padded: true,
				id: targetId,
				width: 300
			} );
			$( element ).append( whoIsOnlinePopup.$element );
		}
		whoIsOnlinePopup.toggle( true );
		$( element ).attr( 'aria-expanded', true );
	}

	function closePopup( element ) {
		whoIsOnlinePopup.toggle( false );
		$( element ).attr( 'aria-expanded', false );
	}

	$( '.wo-link' ).on( 'mouseover click', ( e ) => {
		showPopup( e.currentTarget );
	} );

	$( '.wo-link' ).on( 'mouseleave', ( e ) => {
		closePopup( e.currentTarget );
	} );

	$( '.wo-link' ).on( 'keydown', ( e ) => {
		if ( e.key === 'Enter' || e.key === ' ' ) {
			e.preventDefault();
			showPopup( e.currentTarget );
		}
		if ( e.key === 'Escape' && whoIsOnlinePopup ) {
			closePopup( e.currentTarget );
		}
	} );
}( mediaWiki, jQuery ) );
