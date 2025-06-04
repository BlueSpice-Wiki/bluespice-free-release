ve.ui.AttachmentInspectorTool = function VeUiAttachmentInspectorTool( toolGroup, config ) {
	ve.ui.AttachmentInspectorTool.super.call( this, toolGroup, config );
};

OO.inheritClass( ve.ui.AttachmentInspectorTool, ve.ui.FragmentInspectorTool );

ve.ui.AttachmentInspectorTool.static.name = 'attachmentTool';
ve.ui.AttachmentInspectorTool.static.group = 'none';
ve.ui.AttachmentInspectorTool.static.autoAddToCatchall = false;
ve.ui.AttachmentInspectorTool.static.icon = 'attachment';
ve.ui.AttachmentInspectorTool.static.title = OO.ui.deferMsg(
	'enhancedupload-ve-attachment-title'
);
ve.ui.AttachmentInspectorTool.static.modelClasses = [ ve.dm.AttachmentNode ];
ve.ui.AttachmentInspectorTool.static.commandName = 'attachmentCommand';

ve.ui.toolFactory.register( ve.ui.AttachmentInspectorTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'attachmentCommand', 'window', 'open',
		{ args: [ 'attachmentInspector' ], supportedSelections: [ 'linear' ] }
	)
);
