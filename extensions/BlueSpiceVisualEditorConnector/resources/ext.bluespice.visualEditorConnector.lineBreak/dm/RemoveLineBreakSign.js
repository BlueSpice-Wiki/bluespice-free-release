bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.RemoveLineBreakSign = function () {
	// Parent constructor
	bs.vec.dm.RemoveLineBreakSign.super.apply( this, arguments );
};

/* Inheritance */
OO.inheritClass( bs.vec.dm.RemoveLineBreakSign, ve.dm.BreakNode );

bs.vec.dm.RemoveLineBreakSign.static.toDataElement = function ( domElement, doc ) {
	try {
		const nextSibling = domElement[ 0 ].nextSibling;

		if ( nextSibling && nextSibling.nodeType === Node.TEXT_NODE ) {
			const textContent = nextSibling.textContent;

			if ( textContent.startsWith( '\n' ) ) {
				// Replace newline with zero-width space
				nextSibling.textContent = '\u200B' + textContent.slice( 1 );
			}
		}
	} catch ( error ) {
		console.error( 'BSVEC RemoveLineBreakSign error: ' + error ); // eslint-disable-line no-console
	}

	// Parent method
	return bs.vec.dm.RemoveLineBreakSign.parent.static.toDataElement.call( this, domElement, doc );
};

bs.vec.dm.RemoveLineBreakSign.static.toDomElements = function ( dataElement, doc ) {
	try {
		const brElements = doc.body.getElementsByTagName( 'br' );

		Array.from( brElements ).forEach( ( brElement ) => {
			const nextSibling = brElement.nextSibling;

			if ( nextSibling && nextSibling.nodeType === Node.TEXT_NODE ) {
				const textContent = nextSibling.textContent;

				if ( textContent.startsWith( '\u200B' ) ) {
					// Replace zero-width space with newline
					nextSibling.textContent = '\n' + textContent.slice( 1 );
				}
			}
		} );
	} catch ( error ) {
		console.error( 'BSVEC RemoveLineBreakSign error: ' + error ); // eslint-disable-line no-console
	}

	// Parent method
	return bs.vec.dm.RemoveLineBreakSign.parent.static.toDomElements.call( this, dataElement, doc );
};

/* Registration */
ve.dm.modelRegistry.register( bs.vec.dm.RemoveLineBreakSign );
