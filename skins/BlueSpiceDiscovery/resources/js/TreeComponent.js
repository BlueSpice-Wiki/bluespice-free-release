( function ( mw, $ ) {
	// See https://github.com/hallowelt/mwstake-mediawiki-component-commonuserinterface/blob/master/tests/phpunit/TreeDataGeneratorTest.php#L32
	// and BlueSpiceDiscovery/src/Component/EnhancedSidebarTree.php

	// This script is responsible for setting the cookie. Functionality of the tree is done in
	// https://github.com/hallowelt/mwstake-mediawiki-component-commonuserinterface/blob/master/resources/tree/tree.js
	$( '.tree-component' ).on( 'click', '.mws-tree-expander', function () {
		const cntId = getContainerId( this );
		const cookieContent = getCookieContent( cntId );
		const expandedBeforeClick = $( this ).attr( 'aria-expanded' );
		const $items = $( this ).parents( 'li' );
		const path = makePath( $items );

		let newCookieContent = [];
		if ( expandedBeforeClick === 'false' ) {
			newCookieContent = addPath( path, cookieContent );
		} else {
			newCookieContent = removePath( path, cookieContent );
		}

		setCookieContent( cntId, newCookieContent );
	} );

	function getContainerId( clickEl ) {
		const cnt = $( clickEl ).parents( '.mws-tree.root ' ).first();

		return $( cnt ).attr( 'id' );
	}

	function getCookieContent( cntId ) {
		let cookieContent = discovery_cookie.get( cntId );

		if ( !cookieContent ) {
			cookieContent = [];
		}

		return cookieContent;
	}

	function setCookieContent( cntId, cookieContent ) {
		discovery_cookie.set( cntId, cookieContent );
	}

	function makePath( $items ) {
		const paths = [];
		let item = '';
		let id = '';
		for ( let index = ( $items.length - 1 ); index >= 0; index-- ) {
			item = $items[ index ];
			id = $( item ).attr( 'id' );

			paths.push( id );
		}

		return paths.join( '/' );
	}

	function addPath( path, cookieContent ) {
		const paths = removePath( path, cookieContent );
		paths.push( path );

		return paths;
	}

	function removePath( path, cookieContent ) {
		const paths = [];
		let curPath = '';
		for ( let index = 0; index < cookieContent.length; index++ ) {
			curPath = cookieContent[ index ];
			if ( curPath.slice( 0, path.length ) !== path ) {
				paths.push( curPath );
			}
		}

		return paths;
	}
}( mediaWiki, jQuery ) );
