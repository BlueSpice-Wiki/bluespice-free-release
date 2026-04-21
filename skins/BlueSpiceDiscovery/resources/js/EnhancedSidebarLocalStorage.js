$( () => {

	const items = localStorage.getItem( 'expanded-navigation-tree' );
	if ( !items ) {
		return;
	}
	const itemsList = JSON.parse( items );
	const itemsIDs = Object.keys( itemsList );
	for ( const id in itemsIDs ) {
		const relatedNode = document.getElementById( itemsIDs[ id ] );
		if ( relatedNode ) {
			continue;
		}
		delete ( itemsList[ itemsIDs[ id ] ] );
	}
	if ( Object.keys( itemsList ).length === 0 ) {
		localStorage.removeItem( 'expanded-navigation-tree' );
	} else {
		localStorage.setItem( 'expanded-navigation-tree', JSON.stringify( itemsList ) );
	}

} );
