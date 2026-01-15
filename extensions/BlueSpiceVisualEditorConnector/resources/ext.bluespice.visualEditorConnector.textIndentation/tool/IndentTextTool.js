bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.IndentTextTool = function () {
	bs.vec.ui.IndentTextTool.super.apply( this, arguments );
};
OO.inheritClass( bs.vec.ui.IndentTextTool, ve.ui.AnnotationTool );
bs.vec.ui.IndentTextTool.static.name = 'indent-text';
bs.vec.ui.IndentTextTool.static.group = 'textStyle';
bs.vec.ui.IndentTextTool.static.icon = 'indent';
bs.vec.ui.IndentTextTool.static.title =
	OO.ui.deferMsg( 'bs-visualeditorconnector-text-style-indent-label' );
bs.vec.ui.IndentTextTool.static.annotation = { name: 'textStyle/indent-text' };
bs.vec.ui.IndentTextTool.static.commandName = 'indent-text';
ve.ui.toolFactory.register( bs.vec.ui.IndentTextTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'indent-text', 'annotation', 'toggle',
		{ args: [ 'textStyle/indent-text' ], supportedSelections: [ 'linear' ] }
	)
);
