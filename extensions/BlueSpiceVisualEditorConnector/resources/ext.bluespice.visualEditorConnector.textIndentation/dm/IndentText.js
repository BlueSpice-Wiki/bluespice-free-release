bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.IndentText = function ( element, store ) {
	// Parent constructor
	bs.vec.dm.IndentText.super.apply( this, [ element, store ] );
};

/* Inheritance */

OO.inheritClass( bs.vec.dm.IndentText, ve.dm.Annotation );

/* Static Properties */

bs.vec.dm.IndentText.static.name = 'textStyle/indent-text';

bs.vec.dm.IndentText.static.applyToAppendedContent = true;

bs.vec.dm.IndentText.static.toDomElements = function ( data, doc ) {
	// Create <dl><dd></dd></dl> structure
	const dl = doc.createElement( 'dl' ),
		dd = doc.createElement( 'dd' );

	dl.appendChild( dd );
	return [ dl ];
};

/* Registration */

ve.dm.modelRegistry.register( bs.vec.dm.IndentText );
