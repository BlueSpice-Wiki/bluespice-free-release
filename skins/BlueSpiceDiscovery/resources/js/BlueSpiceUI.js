( function ( d, $, mw ) {
	init();

	$( d ).on( 'click', '.sb-toggle', function ( e ) {
		// TODO: This has to be improved. It will only work as long
		// bootstrap scripts run before this script runs
		e.preventDefault();
		e.stopPropagation();

		const controls = $( this ).attr( 'aria-controls' );

		if ( controls === 'sb-pri-cnt' || controls === 'sb-sec-cnt' ) {
			const expanded = $( this ).attr( 'aria-expanded' );
			if ( expanded === 'true' ) {
				if ( $( window ).width() < 1400 ) {
					$( 'body ' ).css( 'overflow', '' );
				}

				toggleSidebar( controls );

				if ( controls === 'sb-sec-cnt' ) {
					$( '#back-to-top' ).removeClass( 'collapsed' );
				}
			}
			if ( expanded === 'false' ) {
				if ( $( window ).width() < 1400 ) {
					$( 'body ' ).css( 'overflow-x', 'hidden' );
				}

				toggleSidebar( controls );

				if ( controls === 'sb-sec-cnt' ) {
					$( '#back-to-top' ).addClass( 'collapsed' );
				}
			}
		}
	} );

	$( d ).on( 'click', '#full-screen-btn', function ( e ) {
		e.preventDefault();

		if ( $( this ).hasClass( 'fs-mode-enabled' ) ) {
			disableFullscreenMode( this );
		} else {
			enableFullscreenMode( this );
		}
	} );

	function enableFullscreenMode( element ) {
		$( element ).addClass( 'fs-mode-enabled' );
		$( element ).removeClass( 'bi-chevron-expand' );
		$( element ).addClass( 'bi-chevron-contract' );
		$( element ).attr(
			'title',
			mw.message( 'bs-discovery-navbar-full-screen-button-disable-title' ).text()
		);
		$( element ).attr(
			'aria-label',
			mw.message( 'bs-discovery-navbar-full-screen-button-disable-aria-label' ).text()
		);

		fullscreenModePreserveSidebarState();

		hideSidebarPrimary();
		hideSidebarSecondary();
		resizeTitleLine();

		$( 'body' ).addClass( 'fs-mode-enabled' );

		const width = $( '#main' ).width();
		resizeTitleLine( width );

		addToBodyClassesCookie( { fsMode: 'fs-mode-enabled' } );
		discovery_cookie.set( 'fsMode', 'true' );
	}

	function resizeTitleLine( width ) {
		$( '#title-line > div' ).width( width );
	}

	function disableFullscreenMode( element ) {
		$( element ).removeClass( 'fs-mode-enabled' );
		$( element ).removeClass( 'bi-chevron-contract' );
		$( element ).addClass( 'bi-chevron-expand' );
		$( element ).attr(
			'title',
			mw.message( 'bs-discovery-navbar-full-screen-button-enable-title' ).text()
		);
		$( element ).attr(
			'aria-label',
			mw.message( 'bs-discovery-navbar-full-screen-button-enable-aria-label' ).text()
		);

		fullscreenModeRestoreSidebarState();

		$( 'body' ).removeClass( 'fs-mode-enabled' );

		const width = $( '#main' ).width();
		resizeTitleLine( width );

		removeFromBodyClassesCookie( 'fsMode' );
		discovery_cookie.set( 'fsMode', 'false' );
	}

	function fullscreenModePreserveSidebarState() {
		const preserve_obj = {};

		const sbPriState = discovery_cookie.get( 'sb-pri-cnt' );
		Object.assign( preserve_obj, { 'sb-pri-cnt': sbPriState } );

		const sbSecState = discovery_cookie.get( 'sb-sec-cnt' );
		Object.assign( preserve_obj, { 'sb-sec-cnt': sbSecState } );

		discovery_cookie.set(
			'fsPreserve',
			JSON.stringify( preserve_obj )
		);
	}

	function fullscreenModeRestoreSidebarState() {
		const preserve = discovery_cookie.get( 'fsPreserve' );
		let preserve_obj = {};
		if ( preserve ) {
			preserve_obj = JSON.parse( preserve );

			for ( const id in preserve_obj ) {
				const sbState = discovery_cookie.get( id );

				if ( sbState === 'false' ) {
					toggleSidebar( id, preserve_obj[ id ] );
				} else {
					// if the sidebar was collapsed but is open now it should stay open
					toggleSidebar( id, sbState );
				}
			}
		}
	}

	function addToBodyClassesCookie( obj ) {
		let classes_obj = {};
		const classes = discovery_cookie.get( 'bodyClasses' );
		if ( classes ) {
			classes_obj = JSON.parse( classes );
		}

		Object.assign( classes_obj, obj );

		discovery_cookie.set(
			'bodyClasses',
			JSON.stringify( classes_obj )
		);
	}

	function removeFromBodyClassesCookie( key ) {
		const classes = discovery_cookie.get( 'bodyClasses' );

		if ( classes ) {
			const classes_obj = JSON.parse( classes );

			if ( classes_obj.hasOwnProperty( key ) ) {
				delete classes_obj[ key ];

				discovery_cookie.set(
					'bodyClasses',
					JSON.stringify( classes_obj )
				);
			}
		}
	}

	function init() {
		if ( $( window ).width() >= 1400 && discovery_cookie.get( 'sb-pri-cnt' ) !== 'false' ) {
			showSidebarPrimary();
		}
		if ( $( window ).width() >= 1400 && $( '#book-navigation-panel.active' ).length ) {
			showSidebarPrimary();
		}
		if ( $( window ).width() < 1400 ) {
			hideSidebarPrimary();
			hideSidebarSecondary();
		}
		if ( $( '#sb-sec-cnt.show' ).length ) {
			$( '#back-to-top' ).addClass( 'collapsed' );
		}
		if ( $( window ).width() < 768 ) {
			$( 'html' ).addClass( 'discovery-mobile' );
		} else {
			$( 'html' ).removeClass( 'discovery-mobile' );
		}
		if ( $( '#sb-sec-cnt.show' ).length ) {
			$( '#back-to-top' ).addClass( 'collapsed' );
		}

		const resizeObserver = new ResizeObserver( () => { // eslint-disable-line compat/compat
			if ( $( window ).width() < 768 ) {
				$( 'html' ).addClass( 'discovery-mobile' );
			} else {
				$( 'html' ).removeClass( 'discovery-mobile' );
			}
		} );
		resizeObserver.observe( $( 'html' )[ 0 ] );
	}

	function toggleSidebar( id, expand ) {
		const $toggleBtns = $( '.sb-toggle[aria-controls=' + id + ']' );
		const $sidebarCnt = $( '#' + id );

		// Allow only 'undefinded' or strings 'true' or 'false'
		if ( expand !== 'true' && expand !== 'false' ) {
			expand = undefined;
		}

		if ( expand === undefined ) {
			expand = 'true';
			if ( $sidebarCnt.hasClass( 'show' ) ) {
				expand = 'false';
			}
		}

		let state = '';
		if ( expand === 'true' ) {
			$sidebarCnt.removeClass( 'collapse' );
			$sidebarCnt.addClass( 'show' );
			state = 'hide';

		} else {
			$sidebarCnt.removeClass( 'show' );
			$sidebarCnt.addClass( 'collapse' );
			state = 'show';
		}

		let name = '';
		if ( id === 'sb-pri-cnt' ) {
			name = 'primary';
		}
		if ( id === 'sb-sec-cnt' ) {
			name = 'secondary';
		}

		if ( ( name !== '' ) && ( $toggleBtns.length > 0 ) ) {
			$toggleBtns.attr( 'title',
				mw.message( 'bs-discovery-sidebar-' + name + '-toggle-' + state + '-title' ).text() // eslint-disable-line mediawiki/msg-doc
			);
			$toggleBtns.attr( 'aria-label',
				mw.message( 'bs-discovery-sidebar-' + name + '-toggle-' + state + '-aria-label' ).text() // eslint-disable-line mediawiki/msg-doc
			);

			$toggleBtns.attr( 'aria-expanded', expand );
		}

		discovery_cookie.set( id, expand );
	}

	function showSidebarPrimary() {
		toggleSidebar( 'sb-pri-cnt', 'true' );
	}

	function hideSidebarPrimary() {
		toggleSidebar( 'sb-pri-cnt', 'false' );
	}

	function showSidebarSecondary() {
		toggleSidebar( 'sb-sec-cnt', 'true' );
	}

	function hideSidebarSecondary() {
		toggleSidebar( 'sb-sec-cnt', 'false' );
	}

	/*
	// This click handler should be removed. On page load this is done in init method.
	// and may cause an issue with tree component.

	$( d ).on( 'click', '#sb-pri-cnt, #sb-sec-cnt', function( e ) {
		if ( $( window ).width() < 1400 ) {
			hideSidebarPrimary();
			hideSidebarSecondary();
		}
	});
	*/

	// Fix for new content dropdown focus if dialog is opened - ERM36346
	$( '#new-content-itms .dropdown-item' ).on( 'click', () => {
		$( '#new-content-menu-btn' ).trigger( 'focus' );
	} );

	blueSpiceDiscovery.ui.toggleSidebar = toggleSidebar;
	blueSpiceDiscovery.ui.showSidebarPrimary = showSidebarPrimary;
	blueSpiceDiscovery.ui.hideSidebarPrimary = hideSidebarPrimary;
	blueSpiceDiscovery.ui.showSidebarSecondary = showSidebarSecondary;
	blueSpiceDiscovery.ui.hideSidebarSecondary = hideSidebarSecondary;
	blueSpiceDiscovery.ui.enterFullscreenMode = enableFullscreenMode;
	blueSpiceDiscovery.ui.exitFullscreenMode = disableFullscreenMode;

}( document, jQuery, mediaWiki ) );
