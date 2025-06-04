ext.menueditor.ui.tools.NewItemTool = function () {
	ext.menueditor.ui.tools.NewItemTool.super.apply( this, arguments );
};

OO.inheritClass( ext.menueditor.ui.tools.NewItemTool, OO.ui.Tool );
ext.menueditor.ui.tools.NewItemTool.static.name = 'newItem';
ext.menueditor.ui.tools.NewItemTool.static.icon = 'add';
ext.menueditor.ui.tools.NewItemTool.static.title = mw.message( 'menueditor-toolbar-add-label' );
ext.menueditor.ui.tools.NewItemTool.static.label = mw.message( 'menueditor-toolbar-add-label' );

ext.menueditor.ui.tools.NewItemTool.static.flags = [ 'progressive' ];
ext.menueditor.ui.tools.NewItemTool.static.displayBothIconAndLabel = true;

ext.menueditor.ui.tools.NewItemTool.prototype.onSelect = function () {
	this.setActive( false );
	this.toolbar.emit( 'newItem' );
	this.toolbar.emit( 'updateState' );
};
ext.menueditor.ui.tools.NewItemTool.prototype.onUpdateState = function () {};
