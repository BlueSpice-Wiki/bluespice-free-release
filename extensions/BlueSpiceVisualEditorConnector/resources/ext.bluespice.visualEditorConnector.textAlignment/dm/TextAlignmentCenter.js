bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.TextAlignmentCenter = function ( element, store ) {
	// Parent constructor
	bs.vec.dm.TextAlignmentCenter.super.apply( this, [ element, store ] );
};

/* Inheritance */

OO.inheritClass( bs.vec.dm.TextAlignmentCenter, ve.dm.Annotation );

/* Static Properties */

bs.vec.dm.TextAlignmentCenter.static.name = 'textStyle/align-center';

bs.vec.dm.TextAlignmentCenter.static.matchTagNames = [ 'span' ];
bs.vec.dm.TextAlignmentCenter.static.matchFunction = function ( domElement ) {
	return domElement.style.textAlign === 'center';
};

bs.vec.dm.TextAlignmentCenter.static.applyToAppendedContent = true;

bs.vec.dm.TextAlignmentCenter.static.toDomElements = function ( dataElement, doc ) {
	// Cannot wrap in <p> because it will be removed by the sanitizer
	const el = doc.createElement( 'span' );
	el.style.textAlign = 'center';
	el.style.display = 'block';
	return [ el ];
};

/* Registration */

ve.dm.modelRegistry.register( bs.vec.dm.TextAlignmentCenter );
