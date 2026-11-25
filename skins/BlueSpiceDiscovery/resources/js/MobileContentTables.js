( function ( $ ) {

	$( () => {
		setResponsive();
	} );

	window.addEventListener( 'resize', setResponsive );

	function isTableTooWide( table ) {
		// Temporarily remove responsive classes so we measure the natural table width
		const hadResponsive = table.classList.contains( 'responsive-table' );
		const hadGallery = table.classList.contains( 'responsive-gallery-table' );
		table.classList.remove( 'responsive-table', 'responsive-gallery-table' );

		const $table = $( table );
		const wasWrapped = $table.parent().hasClass( 'responsive-table-scrollable' );
		if ( wasWrapped ) {
			$table.unwrap();
		}

		const tableWidth = table.scrollWidth;
		const containerWidth = table.parentElement ? table.parentElement.clientWidth : window.innerWidth;

		// Restore state
		if ( wasWrapped ) {
			$table.wrap( '<div class="responsive-table-scrollable"></div>' );
		}
		if ( hadResponsive ) {
			table.classList.add( 'responsive-table' );
		}
		if ( hadGallery ) {
			table.classList.add( 'responsive-gallery-table' );
		}

		return tableWidth > containerWidth;
	}

	function setResponsive() {
		const doctables = document.getElementsByTagName( 'table' );

		for ( let i = 0; i < doctables.length; i++ ) {
			const table = doctables[ i ];
			const headers = table.getElementsByTagName( 'th' );
			const hasHeaders = headers.length > 0;
			const responsiveClass = hasHeaders ? 'responsive-table' : 'responsive-gallery-table';
			const $table = $( table );

			if ( isTableTooWide( table ) ) {
				if ( !table.classList.contains( responsiveClass ) ) {
					// * responsive-table
					// * responsive-gallery-table
					table.classList.add( responsiveClass );
				}
				if ( !$table.parent().hasClass( 'responsive-table-scrollable' ) ) {
					$table.wrap( '<div class="responsive-table-scrollable"></div>' );
				}
			} else {
				if ( $table.parent().hasClass( 'responsive-table-scrollable' ) ) {
					$table.unwrap();
				}
				// * responsive-table
				// * responsive-gallery-table
				$table.removeClass( responsiveClass ).filter( '[class=""]' ).removeAttr( 'class' );
			}
		}
	}

}( jQuery ) );
