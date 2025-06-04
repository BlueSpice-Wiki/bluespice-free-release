ext = window.ext || {};
ext.visualEditorPlus = ext.visualEditorPlus || {};
ext.visualEditorPlus.ui = ext.visualEditorPlus.ui || {};

ext.visualEditorPlus.ui.UnderlineItalicSequence = function ExtVisualEditorPlusUiUnderlineItalicSequence() {
	this.wrapperSequenceName = 'underlineItalic';
	this.wrapperCommandName = 'italic';
	this.wrapperWrapChar = '_';
	ext.visualEditorPlus.ui.UnderlineItalicSequence.super.apply( this );
};
OO.inheritClass( ext.visualEditorPlus.ui.UnderlineItalicSequence, ext.visualEditorPlus.ui.WrapperSequenceBase );

ext.visualEditorPlus.ui.UnderlineItalicSequence.prototype.execute = function ( surface, range ) { // eslint-disable-line no-unused-vars
	if ( surface.getMode() !== 'visual' ) {
		return null;
	}
	return ext.visualEditorPlus.ui.UnderlineItalicSequence.super.prototype.execute.apply( this, arguments );
};
