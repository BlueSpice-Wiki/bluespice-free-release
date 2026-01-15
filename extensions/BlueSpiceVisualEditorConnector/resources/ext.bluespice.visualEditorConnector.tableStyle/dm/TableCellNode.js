bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.TableCellNode = function () {
	bs.vec.dm.TableCellNode.super.apply( this, arguments );
	if ( this.element.attributes.style && Array.isArray( this.element.attributes.style ) ) {
		// Wierd stuff
		this.element.attributes.style = this.element.attributes.style[ 0 ];
	}
};

/* Inheritance */

OO.inheritClass( bs.vec.dm.TableCellNode, ve.dm.TableCellNode );

/* Static Methods */

bs.vec.dm.TableCellNode.static.toDataElement = function ( domElements ) {
	const attributes = {};
	let result = {};

	ve.dm.TableCellableNode.static.setAttributes( attributes, domElements );
	this.runTableStyles( 'toDataElement', [ domElements[ 0 ], result ] );

	result = $.extend( true, {}, result, {
		type: this.name,
		attributes: attributes
	} );
	return result;
};

bs.vec.dm.TableCellNode.static.createData = function ( options ) {
	options = options || {};
	const opening = {
		type: 'tableCell',
		attributes: {
			style: options.style || 'data',
			rowspan: options.rowspan || 1,
			colspan: options.colspan || 1
		}
	};
	const content = options.content || [
		{ type: 'paragraph', internal: { generated: 'wrapper' } },
		{ type: '/paragraph' }
	];
	return [ opening ].concat( content ).concat( [ { type: '/tableCell' } ] );
};

bs.vec.dm.TableCellNode.static.toDomElements = function ( dataElement, doc ) {
	const registry = bs.vec.registry.TableStyle.registry;
	for ( const tableStyleKey in registry ) {
		if ( registry[ tableStyleKey ].applyTo !== 'cell' ) {
			continue;
		}
		if ( dataElement.hasOwnProperty( tableStyleKey ) ) {
			const style = registry[ tableStyleKey ].getAttribute();
			const value = dataElement[ tableStyleKey ];
			if ( typeof value !== 'string' || value === '' ) {
				continue;
			}

			dataElement.externalStyle[ style ] = value;
		}
	}

	const tag = dataElement.attributes && dataElement.attributes.style === 'header' ? 'th' : 'td',
		domElement = doc.createElement( tag ),
		attributes = dataElement.attributes;

	ve.dm.TableCellableNode.static.applyAttributes( attributes, domElement );
	this.runTableStyles( 'toDomElements', [ dataElement, domElement, attributes ] );

	const styleParser = new bs.vec.util.StyleAttributeParser( domElement.getAttribute( 'style' ) ),
		externalStyle = dataElement.externalStyle || {};

	if ( Object.keys( externalStyle ).length > 0 ) {
		for ( const styleKey in externalStyle ) {
			if ( !externalStyle.hasOwnProperty( styleKey ) ) {
				continue;
			}

			if ( styleParser.getValueForAttr( styleKey ) !== null ) {
				continue;
			}
			styleParser.addToStyle( styleKey, externalStyle[ styleKey ] );
		}
	}

	domElement.setAttribute( 'style', styleParser.toString() );

	return [ domElement ];
};

bs.vec.dm.TableCellNode.static.runTableStyles = function ( func, params ) {
	const registry = bs.vec.registry.TableStyle.registry;
	let callback = ( function () {} );
	let scope = null;
	const callbackParams = [ 'cell' ].concat( params );
	let tableStyleKey;

	for ( tableStyleKey in registry ) {
		if ( !registry.hasOwnProperty( tableStyleKey ) ) {
			continue;
		}

		scope = registry[ tableStyleKey ];
		callback = scope[ func ];
		callback.apply( scope, callbackParams );
	}
};

bs.vec.dm.TableCellNode.prototype.reportChanged = function () {
	this.emit( 'attributeChange' );
};

ve.dm.modelRegistry.register( bs.vec.dm.TableCellNode );
