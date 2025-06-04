bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.TextAlignmentCenterTool = function () {
	bs.vec.ui.TextAlignmentCenterTool.super.apply( this, arguments );
};
OO.inheritClass( bs.vec.ui.TextAlignmentCenterTool, ve.ui.AnnotationTool );
bs.vec.ui.TextAlignmentCenterTool.static.name = 'align-center';
bs.vec.ui.TextAlignmentCenterTool.static.group = 'textStyle';
bs.vec.ui.TextAlignmentCenterTool.static.icon = 'alignCenter';
bs.vec.ui.TextAlignmentCenterTool.static.title =
	OO.ui.deferMsg( 'bs-visualeditorconnector-text-style-tool-align-center-label' );
bs.vec.ui.TextAlignmentCenterTool.static.annotation = { name: 'textStyle/align-center' };
bs.vec.ui.TextAlignmentCenterTool.static.commandName = 'align-center';
ve.ui.toolFactory.register( bs.vec.ui.TextAlignmentCenterTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'align-center', 'annotation', 'toggle',
		{ args: [ 'textStyle/align-center' ], supportedSelections: [ 'linear' ] }
	)
);
