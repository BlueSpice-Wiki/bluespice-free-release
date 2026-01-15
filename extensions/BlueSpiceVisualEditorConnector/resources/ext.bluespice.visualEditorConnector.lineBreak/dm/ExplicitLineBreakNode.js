bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.ExplicitLineBreak = function () {
	// Parent InternalFileLinkAnnotation
	bs.vec.dm.ExplicitLineBreak.super.apply( this, arguments );
};

OO.inheritClass( bs.vec.dm.ExplicitLineBreak, ve.dm.BreakNode );

bs.vec.dm.ExplicitLineBreak.static.name = 'explicitBreak';
bs.vec.dm.ExplicitLineBreak.static.matchFunction = function ( element ) { // eslint-disable-line no-unused-vars
	// We never want to match <br> nodes, that is handled by VE by default
	// if <br> comes from wikitext. This node is only for inserting from Visual mode
	return false;
};

bs.vec.dm.ExplicitLineBreak.static.toDomElements = function ( dataElement, doc ) {
	const node = doc.createElement( 'br' );
	// Add this explicitly to "mark" the break as coming from HTML, so that it won't get stripped by Parsoid
	node.setAttribute( 'data-parsoid', JSON.stringify( { stx: 'html' } ) );
	return [ node ];
};

/* Registration */
ve.dm.modelRegistry.register( bs.vec.dm.ExplicitLineBreak );
