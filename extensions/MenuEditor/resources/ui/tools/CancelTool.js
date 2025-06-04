ext.menueditor.ui.tools.CancelTool = function () {
	ext.menueditor.ui.tools.CancelTool.super.apply( this, arguments );
};
OO.inheritClass( ext.menueditor.ui.tools.CancelTool, OO.ui.Tool );
ext.menueditor.ui.tools.CancelTool.static.name = 'cancel';
ext.menueditor.ui.tools.CancelTool.static.icon = 'close';
ext.menueditor.ui.tools.CancelTool.static.title = mw.message( 'menueditor-toolbar-cancel' );

ext.menueditor.ui.tools.CancelTool.prototype.onSelect = function () {
	this.setActive( false );
	this.toolbar.emit( 'cancel' );
	this.toolbar.emit( 'updateState' );
};
ext.menueditor.ui.tools.CancelTool.prototype.onUpdateState = function () {};
