bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.TableRowNode = function () {
	bs.vec.dm.TableRowNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.vec.dm.TableRowNode, ve.dm.TableRowNode );

/* Override default data creation */
ve.dm.TableRowNode.static.createData = function ( options ) {
	let i, data = [];

	options = options || {};

	const cellCount = options.cellCount || 1;

	data.push( Object.assign( {
		type: 'tableRow'
	}, options.styles || {} ) );
	for ( i = 0; i < cellCount; i++ ) {
		data = data.concat( ve.dm.TableCellNode.static.createData( options ) );
	}
	data.push( { type: '/tableRow' } );
	return data;
};

ve.dm.TableRowNode.static.applyCellStyling = function ( rowNode, cell, cellIndex, styles ) {
	const data = [];
	data.push( rowNode.element, styles );

	data.push( { type: '/tableRow' } );
	return data;
};

ve.dm.TableRowNode.static.applyStylings = function ( rowNode, styles ) {
	const data = [];
	data.push( Object.assign( rowNode.element, styles || {} ) );
	data.push( { type: '/tableRow' } );
	return data;
};

bs.vec.dm.TableRowNode.static.toDataElement = function ( domElements ) {
	let result = {};

	this.runTableStyles( 'toDataElement', [ domElements[ 0 ], result ] );

	result = Object.assign( result, {
		type: this.name
	} );
	return result;
};

bs.vec.dm.TableRowNode.static.toDomElements = function ( dataElement, doc ) {
	const domElement = doc.createElement( 'tr' );

	this.runTableStyles( 'toDomElements', [ dataElement, domElement ] );

	return [ domElement ];
};

bs.vec.dm.TableRowNode.prototype.reportChanged = function () {
	this.emit( 'attributeChange' );
};

bs.vec.dm.TableRowNode.static.runTableStyles = function ( func, params ) {
	const registry = bs.vec.registry.TableStyle.registry;
	let callback = ( function () {} );
	let scope = null;
	const callbackParams = [ 'row' ].concat( params );
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

ve.dm.modelRegistry.register( bs.vec.dm.TableRowNode );
