( function ( d, $ ) {

	$( d ).on( 'keydown', ( e ) => {
		if ( e.key !== 'Tab' ) {
			return;
		}
		const handled = e.shiftKey ? setFocusBackward( e ) : setFocusForward( e );

		if ( handled ) {
			e.preventDefault();
		}
	} );

	function getFirstMainLink() {
		const $focusMainLinks = $( '#main' ).find( 'a[href]' )
			.filter( ( _, el ) => $( el ).is( ':visible' ) ); // eslint-disable-line no-jquery/no-sizzle
		return $( $focusMainLinks[ 0 ] );
	}

	function setFocusBackward( e ) {
		const srcElement = e.target;
		const $headerLinks = $( '#nb-pri' ).find( 'a' );
		const $focusMainLink = getFirstMainLink();
		const $sidebarPriToggleBtn = $( '#sb-pri-tgl-btn' );
		const $sidebarSecToggleBtn = $( '#sb-sec-tgl-btn' );
		// focus has to be set to sidebar secondary: menu or toggle button
		if ( srcElement === $focusMainLink[ 0 ] ) {
			if ( $( $sidebarSecToggleBtn ).attr( 'aria-expanded' ) === 'false' ) {
				setFocusToElement( e, $sidebarSecToggleBtn );
			} else {
				const $sidebarSecLast = $( '#sb-sec-cnt' ).find( 'a' ).last();
				setFocusToElement( e, $sidebarSecLast );
			}
			return true;
		}
		const primaryToggleIndex = $headerLinks.index( $( '#sb-pri-tgl-btn' ) );
		if ( srcElement === $headerLinks.eq( primaryToggleIndex + 1 )[ 0 ] ) {
			if ( $( $sidebarPriToggleBtn ).attr( 'aria-expanded' ) === 'false' ) {
				setFocusToElement( e, $sidebarPriToggleBtn );
			} else {
				const $sidebarLast = $( '#sb-pri-cnt' ).find( 'a' ).last();
				setFocusToElement( e, $sidebarLast );
			}
			return true;
		}
		if ( srcElement === $( '#sb-pri-cnt' ).find( 'a' ).first()[ 0 ] ) {
			setFocusToElement( e, $sidebarPriToggleBtn );
			return true;
		}
		if ( srcElement === $( '#sb-sec-cnt' ).find( 'a' ).first()[ 0 ] ) {
			setFocusToElement( e, $sidebarSecToggleBtn );
			return true;
		}
		return false;
	}

	function setFocusForward( e ) {
		const srcElement = e.target;
		// check sidebar toggle btns and set focus to first element in sidebars
		if ( $( srcElement ).attr( 'id' ) === 'sb-pri-tgl-btn' || $( srcElement ).attr( 'id' ) === 'sb-sec-tgl-btn' ) {
			if ( $( srcElement ).attr( 'aria-expanded' ) === 'false' ) {
				if ( $( srcElement ).attr( 'id' ) === 'sb-sec-tgl-btn' ) {
					const $focusMainLink = getFirstMainLink();
					setFocusToElement( e, $focusMainLink );
					return true;
				} else {
					return false;
				}
			} else {
				const $sidebarCnt = $( '#' + $( srcElement ).attr( 'aria-controls' ) );
				const $firstFocusableItem = $sidebarCnt.find( 'a' ).first();
				setFocusToElement( e, $firstFocusableItem );
			}
			return true;
		}

		// check last primary sidebar element to set focus back to header
		const $sidebarPriCnt = $( '#sb-pri-cnt' );
		if ( srcElement === $sidebarPriCnt.find( 'a' ).last()[ 0 ] ) {
			const $navlinks = $( '#nb-pri' ).find( 'a' );
			const currentIndex = $navlinks.index( $( '#sb-pri-tgl-btn' ) );
			if ( currentIndex < 0 ) {
				return;
			}
			const $firstFocusableItem = $navlinks.eq( currentIndex + 1 );
			setFocusToElement( e, $firstFocusableItem );
			return true;
		}

		// Check last secondary sidebar element to set focus to main
		const $sidebarSecCnt = $( '#sb-sec-cnt' );
		if ( srcElement === $sidebarSecCnt.find( 'a' ).last()[ 0 ] ) {
			const $focusMainLink = getFirstMainLink();
			setFocusToElement( e, $focusMainLink );
			return true;
		}
		return false;
	}

	function setFocusToElement( e, $element ) {
		e.preventDefault();
		$element.trigger( 'focus' );
	}

}( document, jQuery ) );
