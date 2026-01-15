'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.ui = ext.pdfcreator.ve.ui || {};

ext.pdfcreator.ve.ui.PageBreakInspectorTool = function ( toolGroup, config ) {
	ext.pdfcreator.ve.ui.PageBreakInspectorTool.super.call( this, toolGroup, config );
};

OO.inheritClass( ext.pdfcreator.ve.ui.PageBreakInspectorTool, ve.ui.FragmentWindowTool );

ext.pdfcreator.ve.ui.PageBreakInspectorTool.static.name = 'pagebreakTool';
ext.pdfcreator.ve.ui.PageBreakInspectorTool.static.group = 'none';
ext.pdfcreator.ve.ui.PageBreakInspectorTool.static.autoAddToCatchall = false;
ext.pdfcreator.ve.ui.PageBreakInspectorTool.static.icon = '';
ext.pdfcreator.ve.ui.PageBreakInspectorTool.static.title = mw.message( 'pdfcreator-page-break-tool-title' ).text();
ext.pdfcreator.ve.ui.PageBreakInspectorTool.static.modelClasses = [
	ext.pdfcreator.ve.dm.PagebreakNode
];
ext.pdfcreator.ve.ui.PageBreakInspectorTool.static.commandName = 'pagebreakCommand';

ext.pdfcreator.ve.ui.PageBreakInspectorTool.prototype.onSelect = function () {
	const surface = this.toolbar.getSurface(),
		surfaceModel = surface.getModel(),
		selection = surfaceModel.getSelection(),
		doc = surfaceModel.getDocument();

	const itemData = [
		{ type: 'pdfpagebreak' },
		{ type: '/pdfpagebreak' }
	];

	surfaceModel.change(
		ve.dm.TransactionBuilder.static.newFromInsertion(
			doc,
			selection.getRange().end,
			itemData
		)
	);
};

ve.ui.toolFactory.register( ext.pdfcreator.ve.ui.PageBreakInspectorTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'pagebreakCommand', 'content', 'insert',
		{ args: [
			[
				{ type: 'pdfpagebreak' },
				{ type: '/pdfpagebreak' }
			],
			// annotate
			false,
			// collapseToEnd
			true
		], supportedSelections: [ 'linear' ] }
	)
);
