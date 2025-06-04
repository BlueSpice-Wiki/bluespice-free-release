'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.dm = ext.pdfcreator.ve.dm || {};

ext.pdfcreator.ve.dm.PageBreakNode = function () {
	// Parent constructor
	ext.pdfcreator.ve.dm.PageBreakNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ext.pdfcreator.ve.dm.PageBreakNode, ve.dm.MWExtensionNode );

/* Static members */

ext.pdfcreator.ve.dm.PageBreakNode.static.name = 'pdfpagebreak';

ext.pdfcreator.ve.dm.PageBreakNode.static.tagName = 'pdfpagebreak';

// Name of the parser tag
ext.pdfcreator.ve.dm.PageBreakNode.static.extensionName = 'pdfpagebreak';

// This tag renders without content
ext.pdfcreator.ve.dm.PageBreakNode.static.childNodeTypes = [];
ext.pdfcreator.ve.dm.PageBreakNode.static.isContent = true;

ext.pdfcreator.ve.dm.PageBreakNode.static.toDomElements = function ( dataElement, doc ) {
	const el = doc.createElement( this.tagName );
	el.setAttribute( 'typeof', 'mw:Extension/' + this.getExtensionName( dataElement ) );
	el.setAttribute( 'data-mw', JSON.stringify( {} ) );
	return [ el ];
};

ext.pdfcreator.ve.dm.PageBreakNode.static.getHashObject = function ( dataElement ) {
	return {
		type: dataElement.type,
		mw: []
	};
};

ext.pdfcreator.ve.dm.PageBreakNode.static.cloneElement = function () {
	// Parent method
	const clone = ve.dm.MWExtensionNode.super.static.cloneElement.apply( this, arguments );
	return clone;
};

/* Registration */

ve.dm.modelRegistry.register( ext.pdfcreator.ve.dm.PageBreakNode );
