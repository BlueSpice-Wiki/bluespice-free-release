'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.dm = ext.pdfcreator.ve.dm || {};

ext.pdfcreator.ve.dm.ExcludeExportEndNode = function ExcludeExportEndNode() {
	// Parent constructor
	ext.pdfcreator.ve.dm.ExcludeExportEndNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ext.pdfcreator.ve.dm.ExcludeExportEndNode,
	ext.pdfcreator.ve.dm.ExcludeExportStartNode );

/* Static members */

ext.pdfcreator.ve.dm.ExcludeExportEndNode.static.name = 'pdfexcludeend';

ext.pdfcreator.ve.dm.ExcludeExportEndNode.static.tagName = 'pdfexcludeend';

// Name of the parser tag
ext.pdfcreator.ve.dm.ExcludeExportEndNode.static.extensionName = 'pdfexcludeend';

/* Registration */

ve.dm.modelRegistry.register( ext.pdfcreator.ve.dm.ExcludeExportEndNode );
