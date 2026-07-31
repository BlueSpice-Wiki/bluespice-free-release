( function ( $, mw ) {
	$.extend( $.expr[ ':' ], { // eslint-disable-line no-jquery/no-extend
		containsi: function ( elem, i, match ) {
			return ( elem.textContent || elem.innerText || '' ).toLowerCase()
				.indexOf( ( match[ 3 ] || '' ).toLowerCase() ) >= 0;
		}
	} );
	const filterInput = new OO.ui.SearchInputWidget( {
		placeholder: mw.message( 'filterspecialpages-hint-label' ).text(),
		id: 'filterspecialpages',
		name: 'filterspecialpages'
	} );
	$( '#content' ).prepend( filterInput.$element );
	filterInput.focus();
	filterInput.on( 'change', ( value ) => {
		if ( value.length === 0 ) {
			$( '#content li' ).show();
		} else {
			// display hide all li's where text not in
			$( '#content li:not(:containsi(' + value + '))' ).hide();
			$( '#content li:containsi(' + value + ')' ).show();
		}
		// hide empty sections, show non empty sections
		$( '.mw-specialpages-list ul, .mw-specialpages-notes ul' ).each( function () {
			if ( $( this ).children( ':visible' ).length === 0 ) { // eslint-disable-line no-jquery/no-sizzle
				$( this ).parent().prev().hide();
			} else {
				$( this ).parent().prev().show();
			}
		} );

	} );

	filterInput.$element.find( 'input' ).on( 'keyup', ( e ) => {
		// escape key
		if ( e.keyCode == 27 ) { // eslint-disable-line eqeqeq
			const selection = window.getSelection().toString();
			if ( selection === filterInput.getValue() ) {
				// Cursor to end
				filterInput.focus();
				const tmpStr = filterInput.getValue();
				filterInput.setValue( '' );
				filterInput.setValue( tmpStr );
			} else {
				// select all text
				filterInput.select();
			}
		}
	} );

	filterInput.on( 'enter', () => {
		const visibleLinks = $( '#content li:containsi(' + filterInput.getValue() + ')' ); // eslint-disable-line no-jquery/variable-pattern
		if ( visibleLinks.size() === 1 ) {
			window.location.href = visibleLinks.find( 'a' ).attr( 'href' );
		}
	} );

}( jQuery, mediaWiki ) );
