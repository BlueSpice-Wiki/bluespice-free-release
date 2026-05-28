ext.menueditor.ui.tools.OldRevisionNoticeTool = function () {
	ext.menueditor.ui.tools.OldRevisionNoticeTool.super.apply( this, arguments );
	this.popup = new OO.ui.PopupWidget( {
		$floatableContainer: this.$element,
		head: true,
		width: 320,
		anchor: false,
		align: 'forwards',
		autoClose: true,
		padded: true,
		$autoCloseIgnore: this.$element,
		$content: new OO.ui.MessageWidget( {
			type: 'warning',
			label: mw.message( 'menueditor-ui-oldrevision-notice' ).text()
		} ).$element
	} );

	this.$element.append( this.popup.$element );
	this.popup.toggle( true );
};

OO.inheritClass( ext.menueditor.ui.tools.OldRevisionNoticeTool, OO.ui.PopupTool );
ext.menueditor.ui.tools.OldRevisionNoticeTool.static.name = 'oldRevisionNotice';
ext.menueditor.ui.tools.OldRevisionNoticeTool.static.icon = 'alert';
ext.menueditor.ui.tools.OldRevisionNoticeTool.static.title =
	mw.message( 'menueditor-ui-notices' ).text();
ext.menueditor.ui.tools.OldRevisionNoticeTool.static.autoAddToCatchall = false;
ext.menueditor.ui.tools.OldRevisionNoticeTool.prototype.onUpdateState = function () {};
