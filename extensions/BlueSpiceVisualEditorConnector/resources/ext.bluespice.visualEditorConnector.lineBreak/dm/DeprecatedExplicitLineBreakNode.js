bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.DeprecatedExplicitLineBreak = function () {
	// Parent InternalFileLinkAnnotation
	bs.vec.dm.DeprecatedExplicitLineBreak.super.apply( this, arguments );
};

OO.inheritClass( bs.vec.dm.DeprecatedExplicitLineBreak, bs.vec.dm.ExplicitLineBreak );

bs.vec.dm.DeprecatedExplicitLineBreak.static.name = 'deprecatedExplicitBreak';
bs.vec.dm.DeprecatedExplicitLineBreak.static.matchTagNames = [ 'span' ];
bs.vec.dm.DeprecatedExplicitLineBreak.static.matchFunction = function ( element ) {
	return element.classList.contains( 'bs-vec-line-break' );
};

bs.vec.dm.DeprecatedExplicitLineBreak.static.toDomElements = function ( dataElement, doc ) {
	const nodes = bs.vec.dm.DeprecatedExplicitLineBreak.parent.static.toDomElements.call( this, dataElement, doc );

	// Doesnt seem possible to remove `class` completely. If we do, it is just re-added later on somewhere
	// By removing class name, we at least get `class=""` instead of `class="bs-vec-line-break"`
	nodes[ 0 ].className = '';
	return nodes;
};

/* Registration */
ve.dm.modelRegistry.register( bs.vec.dm.DeprecatedExplicitLineBreak );
