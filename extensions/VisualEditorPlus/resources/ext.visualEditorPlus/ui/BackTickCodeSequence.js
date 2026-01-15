ext = window.ext || {};
ext.visualEditorPlus = ext.visualEditorPlus || {};
ext.visualEditorPlus.ui = ext.visualEditorPlus.ui || {};

ext.visualEditorPlus.ui.BackTickCodeSequence = function ExtVisualEditorPlusUiBackTickCodeSequence() {
	this.wrapperSequenceName = 'backTicksCode';
	this.wrapperCommandName = 'code';
	this.wrapperWrapChar = '`';
	ext.visualEditorPlus.ui.BackTickCodeSequence.super.apply( this );
};
OO.inheritClass( ext.visualEditorPlus.ui.BackTickCodeSequence, ext.visualEditorPlus.ui.WrapperSequenceBase );
