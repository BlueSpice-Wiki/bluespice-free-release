ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.widget = ext.enhancedUI.widget || {};

ext.enhancedUI.widget.HistoryToolbar = function ( config ) {
	config = config || {};
	config.classes = [ 'enhanced-history-toolbar' ];
	ext.enhancedUI.widget.HistoryToolbar.super.call( this,
		new OO.ui.ToolFactory(), new OO.ui.ToolGroupFactory(), config
	);

	this.addNewItemTool();
	this.setup( [
		{
			name: 'compare-action',
			type: 'bar',
			classes: [ 'toolbar-actions' ],
			include: [ 'compare' ],
			align: 'after'
		}
	] );

	this.initialize();
	this.emit( 'updateState' );
};

OO.inheritClass( ext.enhancedUI.widget.HistoryToolbar, OO.ui.Toolbar );

ext.enhancedUI.widget.HistoryToolbar.prototype.addNewItemTool = function () {
	this.toolFactory.register( ext.enhancedUI.widget.CompareTool );
};

/** compare tool to compare versions */
ext.enhancedUI.widget.CompareTool = function () {
	ext.enhancedUI.widget.CompareTool.super.apply( this, arguments );
	this.data = 'compare';
	// Disable initially
	this.setDisabled( true );
};

OO.inheritClass( ext.enhancedUI.widget.CompareTool, OO.ui.Tool );
ext.enhancedUI.widget.CompareTool.static.name = 'compare';
ext.enhancedUI.widget.CompareTool.static.icon = '';
ext.enhancedUI.widget.CompareTool.static.title = mw.message( 'enhanced-standard-uis-history-compare-tool-title' ).text();

ext.enhancedUI.widget.CompareTool.static.flags = [ 'primary', 'progressive' ];
ext.enhancedUI.widget.CompareTool.static.displayBothIconAndLabel = true;
ext.enhancedUI.widget.CompareTool.prototype.onSelect = function () {
	this.setActive( false );
	this.toolbar.emit( 'compare' );
};
ext.enhancedUI.widget.CompareTool.prototype.onUpdateState = function () {};
