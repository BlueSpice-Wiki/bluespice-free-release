ext = window.ext || {};
ext.visualEditorPlus = ext.visualEditorPlus || {};
ext.visualEditorPlus.ui = ext.visualEditorPlus.ui || {};

ext.visualEditorPlus.ui.WrapperSequenceBase = function ExtVisualEditorPlusUiWrapperSequenceBase() {
	ext.visualEditorPlus.ui.WrapperSequenceBase.super.apply( this, [
		this.wrapperSequenceName,
		this.wrapperCommandName,
		new RegExp( '\\s' + this.wrapperWrapChar + '.*?' + this.wrapperWrapChar + '\\s' ),
		undefined,
		{
			setSelection: true
		} ]
	);
};
OO.inheritClass( ext.visualEditorPlus.ui.WrapperSequenceBase, ve.ui.Sequence );

ext.visualEditorPlus.ui.WrapperSequenceBase.prototype.execute = function ( surface, range ) {
	const surfaceModel = surface.getModel(),
		firstWrapperRange = new ve.Range( range.start + 1, range.start + 2 ),
		lastWrapperRange = new ve.Range( range.end - 3, range.end - 1 ),

		firstWrapperFragment = surfaceModel.getLinearFragment( firstWrapperRange, true, true );
	firstWrapperFragment.removeContent();
	const lastWrapperFragment = surfaceModel.getLinearFragment( lastWrapperRange, true, true );
	lastWrapperFragment.removeContent();

	range = new ve.Range( range.start + 1, range.end - 3 );
	const parentReturn = ext.visualEditorPlus.ui.WrapperSequenceBase.super.prototype.execute.apply( this, arguments ),

		newSelection = surfaceModel.getSelection().collapseToEnd(),
		newRange = newSelection.getRange(),
		newFragment = surfaceModel.getLinearFragment( newRange, true, true );

	newFragment.insertContent( ' ' ).collapseToEnd().select();

	return parentReturn;
};
