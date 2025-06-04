bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.DeprecatedExplicitLineBreak = function () {
	// Parent InternalFileLinkAnnotation
	bs.vec.ce.DeprecatedExplicitLineBreak.super.apply( this, arguments );
	this.$element.addClass( 'bs-vec-line-break' );
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.DeprecatedExplicitLineBreak, ve.ce.BreakNode );

/* Static Properties */

bs.vec.ce.DeprecatedExplicitLineBreak.static.name = 'deprecatedExplicitBreak';

/* Registration */
ve.ce.nodeFactory.register( bs.vec.ce.DeprecatedExplicitLineBreak );
