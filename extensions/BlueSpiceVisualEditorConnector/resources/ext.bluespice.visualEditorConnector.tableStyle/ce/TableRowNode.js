bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.TableRowNode = function VeCeTableRowNode( options ) {
	// Parent constructor
	bs.vec.ce.TableRowNode.super.apply( this, arguments );

	const applicator = new bs.vec.ui.TableStyleApplicator( {
		domEl: this.$element
	} );
	applicator.apply( options.element );
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.TableRowNode, ve.ce.TableRowNode );

ve.ce.nodeFactory.register( bs.vec.ce.TableRowNode );
