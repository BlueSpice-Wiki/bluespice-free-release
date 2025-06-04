ext = window.ext || {};
ext.visualEditorPlus = ext.visualEditorPlus || {};
ext.visualEditorPlus.ui = ext.visualEditorPlus.ui || {};

ext.visualEditorPlus.ui.TildeStrikeThroughSequence = function ExtVisualEditorPlusUiTildeStrikeThroughSequence() {
	this.wrapperSequenceName = 'tildeStrikeThrough';
	this.wrapperCommandName = 'strikethrough';
	this.wrapperWrapChar = '~';
	ext.visualEditorPlus.ui.TildeStrikeThroughSequence.super.apply( this );
};
OO.inheritClass( ext.visualEditorPlus.ui.TildeStrikeThroughSequence, ext.visualEditorPlus.ui.WrapperSequenceBase );
