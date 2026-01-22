( function ( mw, $ ) {
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
		if ( !whoIsOnlinePopup ) {
			return;
		}
		whoIsOnlinePopup.toggle( false );
		$( element ).attr( 'aria-expanded', false );
	}

	const $woLinks = $( '.wo-link' );

	$woLinks.on( 'mouseover click', ( e ) => {
		showPopup( e.currentTarget );
	} );

	$woLinks.on( 'mouseleave', ( e ) => {
		closePopup( e.currentTarget );
	} );

	$woLinks.on( 'keydown', ( e ) => {
		if ( e.key === 'Enter' || e.key === ' ' ) {
			e.preventDefault();
			showPopup( e.currentTarget );
		}
		if ( e.key === 'Escape' && whoIsOnlinePopup ) {
			closePopup( e.currentTarget );
		}
	} );
}( mediaWiki, jQuery ) );
