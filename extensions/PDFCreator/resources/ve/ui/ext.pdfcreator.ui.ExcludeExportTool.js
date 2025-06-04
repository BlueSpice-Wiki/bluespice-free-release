'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.ui = ext.pdfcreator.ve.ui || {};

ext.pdfcreator.ve.ui.ExcludeExportTool = function ( toolGroup, config ) {
	ext.pdfcreator.ve.ui.ExcludeExportTool.super.call( this, toolGroup, config );
};

OO.inheritClass( ext.pdfcreator.ve.ui.ExcludeExportTool, ve.ui.FragmentWindowTool );

ext.pdfcreator.ve.ui.ExcludeExportTool.static.name = 'excludeExportTool';
ext.pdfcreator.ve.ui.ExcludeExportTool.static.group = 'insert';
ext.pdfcreator.ve.ui.ExcludeExportTool.static.icon = 'close';
ext.pdfcreator.ve.ui.ExcludeExportTool.static.title = mw.message( 'pdfcreator-exclude-export-tool-title' ).text();

ext.pdfcreator.ve.ui.ExcludeExportTool.static.annotation = { name: 'excludeExport' };

ext.pdfcreator.ve.ui.ExcludeExportTool.static.commandName = 'excludeExportCommand';

ext.pdfcreator.ve.ui.ExcludeExportTool.prototype.onSelect = function () {
	const surface = this.toolbar.getSurface(),
		surfaceModel = surface.getModel(),
		selection = surfaceModel.getSelection(),
		doc = surfaceModel.getDocument();

	// Only in visual mode and if text is selected
	if ( !selection.isCollapsed() && surface.mode === 'visual' ) {
		const itemStart = [
			{ type: 'pdfexcludestart' },
			{ type: '/pdfexcludestart' }
		];
		const itemEnd = [
			{ type: 'pdfexcludeend' },
			{ type: '/pdfexcludeend' }
		];

		// Wrap selected text with tags
		surfaceModel.change(
			ve.dm.TransactionBuilder.static.newFromInsertion(
				doc,
				selection.getRange().start,
				itemStart
			)
		);
		surfaceModel.change(
			ve.dm.TransactionBuilder.static.newFromInsertion(
				doc,
				selection.getRange().end + 2,
				itemEnd
			)
		);
	} else {
		ext.pdfcreator.ve.ui.ExcludeExportTool.super.prototype.onSelect.call( this );
	}
};

ve.ui.toolFactory.register( ext.pdfcreator.ve.ui.ExcludeExportTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'excludeExportCommand', 'content', 'insert',
		{ args: [
			[
				{ type: 'pdfexcludestart' },
				{ type: '/pdfexcludestart' },
				{ type: 'pdfexcludeend' },
				{ type: '/pdfexcludeend' }
			],
			// annotate
			false,
			// collapseToEnd
			true
		], supportedSelections: [ 'linear' ] }
	)
);

if ( ve.ui.wikitextCommandRegistry ) {
	ve.ui.wikitextCommandRegistry.register(
		new ve.ui.Command(
			'excludeExportCommand', 'mwWikitext', 'toggleWrapSelection',
			{ args: [ '<pdfexcludestart />', '<pdfexcludeend />', '' ], supportedSelections: [ 'linear' ] }
		)
	);
}
