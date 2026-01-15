bs.util.registerNamespace( 'ext.bluespice.pageassignments.ui.panel' );

ext.bluespice.pageassignments.ui.panel.Overview = function ( cfg ) {
	ext.bluespice.pageassignments.ui.panel.Overview.super.apply( this, cfg );
	this.$element = $( '<div>' );

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-mypageassignment-store',
		pageSize: 25
	} );

	this.setup();
};

OO.inheritClass( ext.bluespice.pageassignments.ui.panel.Overview, OO.ui.PanelLayout );

ext.bluespice.pageassignments.ui.panel.Overview.prototype.setup = function () {
	const gridCfg = this.setupGridConfig();
	this.grid = new OOJSPlus.ui.data.GridWidget( gridCfg );
	this.$element.append( this.grid.$element );
};

ext.bluespice.pageassignments.ui.panel.Overview.prototype.setupGridConfig = function () {
	const gridCfg = {
		style: 'differentiate-rows',
		columns: {
			page_prefixedtext: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-pageassignments-column-title' ).text(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( val, row ) => new OO.ui.HtmlSnippet( row.page_link )
			}
		},
		store: this.store
	};

	mw.hook( 'BSPageAssignmentsOverviewPanelInit' ).fire( gridCfg );

	return gridCfg;
};
