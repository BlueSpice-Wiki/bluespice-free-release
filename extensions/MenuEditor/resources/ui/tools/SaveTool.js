ext.menueditor.ui.tools.SaveTool = function () {
	ext.menueditor.ui.tools.SaveTool.super.apply( this, arguments );
};

OO.inheritClass( ext.menueditor.ui.tools.SaveTool, OO.ui.Tool );
ext.menueditor.ui.tools.SaveTool.static.name = 'save';
ext.menueditor.ui.tools.SaveTool.static.icon = '';
ext.menueditor.ui.tools.SaveTool.static.title = mw.message( 'menueditor-toolbar-save' );

ext.menueditor.ui.tools.SaveTool.static.flags = [ 'primary', 'progressive' ];
ext.menueditor.ui.tools.SaveTool.static.displayBothIconAndLabel = true;
ext.menueditor.ui.tools.SaveTool.prototype.onSelect = function () {
	this.setActive( false );
	this.toolbar.emit( 'save' );
	this.toolbar.emit( 'updateState' );
};
ext.menueditor.ui.tools.SaveTool.prototype.onUpdateState = function () {};
