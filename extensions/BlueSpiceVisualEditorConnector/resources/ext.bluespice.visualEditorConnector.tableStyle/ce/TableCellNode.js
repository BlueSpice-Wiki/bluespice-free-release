bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.TableCellNode = function VeCeTableCellNode( options ) {
	// Parent constructor
	bs.vec.ce.TableCellNode.super.apply( this, arguments );

	this.applicator = new bs.vec.ui.TableStyleApplicator( {
		domEl: this.$element
	} );
	this.applicator.apply( options.element );
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.TableCellNode, ve.ce.TableCellNode );

bs.vec.ce.TableCellNode.prototype.onAttributeChange = function ( key, val, to ) {
	this.applicator.apply( this.model.element );
	bs.vec.ce.TableCellNode.parent.prototype.onAttributeChange.call( this, key, val, to );
};

ve.ce.nodeFactory.register( bs.vec.ce.TableCellNode );
