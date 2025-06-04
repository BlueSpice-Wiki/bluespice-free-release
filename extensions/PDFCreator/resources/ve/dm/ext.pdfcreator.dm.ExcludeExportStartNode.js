'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.dm = ext.pdfcreator.ve.dm || {};

ext.pdfcreator.ve.dm.ExcludeExportStartNode = function ExcludeExportStartNode() {
	// Parent constructor
	ext.pdfcreator.ve.dm.ExcludeExportStartNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ext.pdfcreator.ve.dm.ExcludeExportStartNode, ve.dm.MWExtensionNode );

/* Static members */

ext.pdfcreator.ve.dm.ExcludeExportStartNode.static.name = 'pdfexcludestart';

ext.pdfcreator.ve.dm.ExcludeExportStartNode.static.tagName = 'pdfexcludestart';

// Name of the parser tag
ext.pdfcreator.ve.dm.ExcludeExportStartNode.static.extensionName = 'pdfexcludestart';

ext.pdfcreator.ve.dm.ExcludeExportStartNode.static.toDomElements = function ( dataElement, doc ) {
	const el = doc.createElement( this.tagName );
	el.setAttribute( 'typeof', 'mw:Extension/' + this.getExtensionName( dataElement ) );
	el.setAttribute( 'data-mw', JSON.stringify( {} ) );
	return [ el ];
};

ext.pdfcreator.ve.dm.ExcludeExportStartNode.static.getHashObject = function ( dataElement ) {
	return {
		type: dataElement.type,
		mw: []
	};
};

ext.pdfcreator.ve.dm.ExcludeExportStartNode.static.cloneElement = function () {
	// Parent method
	const clone = ve.dm.MWExtensionNode.super.static.cloneElement.apply( this, arguments );
	return clone;
};

/* Registration */

ve.dm.modelRegistry.register( ext.pdfcreator.ve.dm.ExcludeExportStartNode );
