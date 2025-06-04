bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.ExplicitLineBreak = function () {
	// Parent InternalFileLinkAnnotation
	bs.vec.ce.ExplicitLineBreak.super.apply( this, arguments );
	this.$element.addClass( 'bs-vec-line-break' );
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.ExplicitLineBreak, ve.ce.BreakNode );

/* Static Properties */

bs.vec.ce.ExplicitLineBreak.static.name = 'explicitBreak';

/* Registration */
ve.ce.nodeFactory.register( bs.vec.ce.ExplicitLineBreak );
