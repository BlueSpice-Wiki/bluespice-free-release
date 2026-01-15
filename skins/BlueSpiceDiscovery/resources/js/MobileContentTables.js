( function ( $ ) {

	$( () => {
		setResponsive();
	} );

	window.onresize = setResponsive;

	function setResponsive() {
		const doctables = document.getElementsByTagName( 'table' );
		let headers;
		if ( window.innerWidth < 1400 ) {
			for ( let i = 0; i < doctables.length; i++ ) {
				headers = doctables[ i ].getElementsByTagName( 'th' );

				if ( headers.length > 0 ) {
					if ( doctables[ i ].classList.contains( 'responsive-table' ) ) {
						continue;
					}
					doctables[ i ].className += ' responsive-table';
				} else {
					if ( doctables[ i ].classList.contains( 'responsive-gallery-table' ) ) {
						continue;
					}
					doctables[ i ].className += ' responsive-gallery-table';
				}
			}

			if ( !$( 'div.responsive-table-scrollable' ).length ) {
				$( 'table.responsive-table' ).wrap( '<div class="responsive-table-scrollable"></div>' );
				$( 'table.responsive-gallery-table' ).wrap( '<div class="responsive-table-scrollable"></div>' );
			}
		} else {
			if ( $( 'div.responsive-table-scrollable' ).length ) {
				$( 'table.responsive-table' ).unwrap();
				$( 'table.responsive-gallery-table' ).unwrap();
			}
			for ( let i = 0; i < doctables.length; i++ ) {
				headers = doctables[ i ].getElementsByTagName( 'th' );
				if ( headers.length > 0 ) {
					$( doctables[ i ] ).removeClass( 'responsive-table' ).filter( '[class=""]' ).removeAttr( 'class' );
				} else {
					$( doctables[ i ] ).removeClass( 'responsive-gallery-table' ).filter( '[class=""]' ).removeAttr( 'class' );
				}
			}
		}
	}

}( jQuery ) );
