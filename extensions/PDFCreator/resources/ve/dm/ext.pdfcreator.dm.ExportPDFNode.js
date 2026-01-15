'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.dm = ext.pdfcreator.ve.dm || {};

ext.pdfcreator.ve.dm.ExportPDFNode = function () {
	// Parent constructor
	ext.pdfcreator.ve.dm.ExportPDFNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ext.pdfcreator.ve.dm.ExportPDFNode, ve.dm.MWInlineExtensionNode );

/* Static members */

ext.pdfcreator.ve.dm.ExportPDFNode.static.name = 'exportpdf';

ext.pdfcreator.ve.dm.ExportPDFNode.static.tagName = 'exportpdf';

// Name of the parser tag
ext.pdfcreator.ve.dm.ExportPDFNode.static.extensionName = 'exportpdf';

// This tag renders without content
ext.pdfcreator.ve.dm.ExportPDFNode.static.childNodeTypes = [];
ext.pdfcreator.ve.dm.ExportPDFNode.static.isContent = true;

/* Registration */

ve.dm.modelRegistry.register( ext.pdfcreator.ve.dm.ExportPDFNode );
