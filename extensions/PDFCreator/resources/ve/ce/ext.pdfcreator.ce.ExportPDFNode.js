'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.ce = ext.pdfcreator.ve.ce || {};

ext.pdfcreator.ve.ce.ExportPDFNode = function () {
	// Parent constructor
	ext.pdfcreator.ve.ce.ExportPDFNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ext.pdfcreator.ve.ce.ExportPDFNode, ve.ce.MWInlineExtensionNode );

/* Static properties */

ext.pdfcreator.ve.ce.ExportPDFNode.static.name = 'exportpdf';

ext.pdfcreator.ve.ce.ExportPDFNode.static.primaryCommandName = 'exportpdfCommand';

// If body is empty, tag does not render anything
ext.pdfcreator.ve.ce.ExportPDFNode.static.rendersEmpty = false;

/* Registration */

ve.ce.nodeFactory.register( ext.pdfcreator.ve.ce.ExportPDFNode );
