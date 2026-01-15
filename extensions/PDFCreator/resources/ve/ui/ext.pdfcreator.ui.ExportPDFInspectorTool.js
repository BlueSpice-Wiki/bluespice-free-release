'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.ui = ext.pdfcreator.ve.ui || {};

ext.pdfcreator.ve.ui.ExportPDFInspectorTool = function ( toolGroup, config ) {
	ext.pdfcreator.ve.ui.ExportPDFInspectorTool.super.call( this, toolGroup, config );
};

OO.inheritClass( ext.pdfcreator.ve.ui.ExportPDFInspectorTool, ve.ui.FragmentInspectorTool );

ext.pdfcreator.ve.ui.ExportPDFInspectorTool.static.name = 'exportpdfTool';
ext.pdfcreator.ve.ui.ExportPDFInspectorTool.static.group = 'none';
ext.pdfcreator.ve.ui.ExportPDFInspectorTool.static.autoAddToCatchall = false;
ext.pdfcreator.ve.ui.ExportPDFInspectorTool.static.icon = '';
ext.pdfcreator.ve.ui.ExportPDFInspectorTool.static.title = mw.message( 'pdfcreator-export-pdf-inspector-export-tool-title' ).text();
ext.pdfcreator.ve.ui.ExportPDFInspectorTool.static.modelClasses = [
	ext.pdfcreator.ve.dm.ExportPDFNode
];
ext.pdfcreator.ve.ui.ExportPDFInspectorTool.static.commandName = 'exportpdfCommand';

ve.ui.toolFactory.register( ext.pdfcreator.ve.ui.ExportPDFInspectorTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'exportpdfCommand', 'window', 'open',
		{ args: [ 'exportpdfInspector' ], supportedSelections: [ 'linear' ] }
	)
);
