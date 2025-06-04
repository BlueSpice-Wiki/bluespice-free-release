window.ext = window.ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.ve = ext.enhancedUI.ve || {};

ext.enhancedUI.ve.MediaDialogFileGrid = function ( component ) { // eslint-disable-line no-unused-vars
	ext.enhancedUI.ve.MediaDialogFileGrid.super.apply( this, arguments );
	this.lastSelectedTab = null;
	this.presetTab = null;
};

OO.inheritClass( ext.enhancedUI.ve.MediaDialogFileGrid, bs.vec.ui.plugin.MWMediaDialog );

ext.enhancedUI.ve.MediaDialogFileGrid.prototype.initialize = function () {
	this.advancedSearchTab = new OO.ui.TabPanelLayout( 'enhanced-stadard-uis-ve-filegrid-panel', {
		label: ve.msg( 'enhanced-standard-uis-ve-filegrid-title' ),
		padded: false
	} );
	this.advancedSearchTab.$element.append( new OO.ui.ProgressBarWidget( { progress: false } ).$element );
	this.fileRepoGrid = null;

	this.component.searchTabs.addTabPanels( [ this.advancedSearchTab ] );

	/**
	 * Unfortunately rendering an ExtJS component within the constructor of an `OO.ui.Widget`
	 * is not always possible, as `OO.ui.Widget::$element` may not be appended to the DOM yet.
	 * Therefore we render the ExtJS component when the user actually opens the tab.
	 */
	this.component.searchTabs.on( 'set', this.onSearchTabsSet, [], this );
	this.component.connect( this, {
		beforePanelSwitch: 'onBeforePanelSwitch',
		panelSwitch: 'onPanelSwitch'
	} );
};

ext.enhancedUI.ve.MediaDialogFileGrid.prototype.onSearchTabsSet = function ( selectedTab ) {
	this.lastSelectedTab = selectedTab.getName();
	if ( selectedTab === this.advancedSearchTab ) {
		this.component.setSize( 'larger' );
		this.component.actions.setAbilities( { cancel: true } );
		this.component.searchTabs.toggleMenu( true );
		this.component.actions.setMode( 'select' );
		this.component.search.runLayoutQueue();

		if ( !this.fileRepoGrid ) {
			this.initFileRepoGrid();
		} else {
			// Reset size on tab switch when already loaded
			this.onFileRepoGridLoaded();
		}
	} else {
		this.component.setBodyHeight( null );
		this.component.updateSize();
	}
};

ext.enhancedUI.ve.MediaDialogFileGrid.prototype.onBeforePanelSwitch = function ( panel ) {
	if ( panel !== 'search' ) {
		return;
	}
	this.presetTab = this.lastSelectedTab;
};

ext.enhancedUI.ve.MediaDialogFileGrid.prototype.onPanelSwitch = function ( panel ) {
	if ( panel === 'search' && this.presetTab === 'enhanced-stadard-uis-ve-filegrid-panel' ) {
		this.component.searchTabs.setTabPanel( this.presetTab );
		this.presetTab = null;
	}
	if ( this.fileRepoGrid ) {
		this.fileRepoGrid.closeFilters();
	}
};

ext.enhancedUI.ve.MediaDialogFileGrid.prototype.initFileRepoGrid = function () {
	this.fileRepoGrid = new ext.enhancedUI.panel.FilelistPanel( {
		rights: [],
		canSwitchModes: false,
		enablePreview: false,
		// Prevent opening a dialog in a dialog, also VE has its own "file info"
		allowFileInfoDialog: false,
		$overlay: this.component.$overlay,
		mediaDialog: true
	} );
	this.fileRepoGrid.grid.connect( this, {
		rowSelected: 'onFileRepoGridSelect',
		datasetChange: 'onFileRepoGridLoaded'
	} );
	this.advancedSearchTab.$element.html( this.fileRepoGrid.$element );
};

ext.enhancedUI.ve.MediaDialogFileGrid.prototype.onFileRepoGridSelect = function ( data ) {
	const row = data.item;
	const imageInfo = {
		title: row.prefixed,
		extmetadata: [],
		user: row.author,
		timestamp: row.formatted_ts,
		descriptionurl: row.url,
		url: row.fileUrl,
		mediatype: row.media_type,
		width: row.width,
		height: row.height,
		thumburl: row.fileUrl,
		thumbwidth: 80
	};
	this.component.chooseImageInfo( imageInfo );
};

ext.enhancedUI.ve.MediaDialogFileGrid.prototype.onFileRepoGridLoaded = function () {
	const height = this.fileRepoGrid.grid.$element.height() + 100;
	this.component.setBodyHeight( height > 910 ? height : 910 );
	this.component.updateSize();
};
