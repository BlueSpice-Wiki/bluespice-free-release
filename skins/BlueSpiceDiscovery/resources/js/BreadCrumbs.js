( function ( d, $, mw ) {

	$( ( e ) => { // eslint-disable-line no-unused-vars
		const dropdownToggles = document.getElementsByClassName( 'breadcrumb-nav-subpages' );

		Array.from( dropdownToggles ).forEach( ( toggle ) => {
			toggle.addEventListener( 'show.bs.dropdown', function () {
				const $breadcrumbItem = $( this ).parent( '.breadcrumb-item' );
				if ( $breadcrumbItem.data( 'loaded' ) === true ) {
					return;
				}
				const path = $( this ).data( 'bs-path' );
				if ( !path ) {
					return;
				}

				const $dropdownMenu = $breadcrumbItem.find( '.dropdown-menu > ul' ).first();

				mw.loader.using( 'mediawiki.api' ).done( () => {
					const api = new mw.Api();
					api.abort();
					api.get( {
						format: 'json',
						action: 'bs-wikisubpage-treestore',
						node: path,
						limit: '-1'
					} )
						.done( ( response ) => {
							for ( let i = 0; i < response.children.length; i++ ) {
								$dropdownMenu.append( '<li>' + response.children[ i ].page_link + '</li>' );
							}
							$breadcrumbItem.data( 'loaded', true );
							const $dropdownItems = $dropdownMenu.find( 'a' );
							for ( let x = 0; x < $dropdownItems.length; x++ ) {
								$( $dropdownItems[ x ] ).addClass( 'dropdown-item' );
							}
						} );
				} );
			} );
		} );
	} );

}( document, jQuery, mediaWiki ) );
