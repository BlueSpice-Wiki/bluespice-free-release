bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.MWTableNode = function BsVecDmMWTableNode() {
	// Parent constructor
	bs.vec.dm.MWTableNode.super.apply( this, arguments );
};

/* Inheritance */
OO.inheritClass( bs.vec.dm.MWTableNode, ve.dm.MWTableNode );

bs.vec.dm.MWTableNode.static.toDataElement = function ( domElements ) {
	const dataElement = ve.dm.MWTableNode.static.toDataElement( domElements ),
		styleParser = new bs.vec.util.StyleAttributeParser( domElements[ 0 ].getAttribute( 'style' ) || '' );

	if ( styleParser.getValueForAttr( 'width' ) !== null && dataElement.hasOwnProperty( 'attributes' ) ) {
		dataElement.attributes.tablewidth = styleParser.getValueForAttr( 'width' );
	}
	if ( styleParser.value && dataElement.hasOwnProperty( 'attributes' ) ) {
		dataElement.attributes.style = styleParser.value;
	}

	return dataElement;
};

bs.vec.dm.MWTableNode.static.toDomElements = function ( dataElement, doc ) {
	const elements = ve.dm.MWTableNode.static.toDomElements( dataElement, doc ),
		element = elements[ 0 ],
		attributes = dataElement.attributes || null;

	if ( !attributes ) {
		return [ element ];
	}

	if ( attributes.style ) {
		element.setAttribute( 'style', attributes.style.toString() );
	}

	if ( attributes.tablewidth ) {
		element.style.width = attributes.tablewidth;
	} else {
		element.style.width = '';
	}

	return [ element ];
};

ve.dm.modelRegistry.register( bs.vec.dm.MWTableNode );
