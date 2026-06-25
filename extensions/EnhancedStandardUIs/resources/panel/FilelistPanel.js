ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.panel = ext.enhancedUI.panel || {};

require( './../widget/FilelistGrid.js' );
require( './../dialog/FileInfoDialog.js' );

ext.enhancedUI.panel.FilelistPanel = function ( cfg ) {
	ext.enhancedUI.panel.FilelistPanel.super.apply( this, cfg );
	this.$element = $( '<div>' ).addClass( 'enhanced-ui-filelist-panel' );

	this.pluginModules = require( './pluginModules.json' );
	this.rights = cfg.rights || [];
	this.canSwitchModes = typeof cfg.canSwitchModes === 'undefined' ? true : cfg.canSwitchModes;
	this.mode = 'list';
	this.$overlay = cfg.$overlay || null;
	this.enablePreview = typeof cfg.enablePreview === 'undefined' ? true : cfg.enablePreview;
	this.allowFileInfoDialog = typeof cfg.allowFileInfoDialog === 'undefined' ? true : cfg.allowFileInfoDialog;
	this.mediaDialog = cfg.mediaDialog || false;

	this.pageSize = 25;
	this.store = new OOJSPlus.ui.data.store.RemoteRestStore( {
		path: 'mws/v1/file-query-store',
		pageSize: this.pageSize,
		sorter: {
			timestamp: { direction: 'desc' }
		}
	} );

	this.setupWidgets();
};

OO.inheritClass( ext.enhancedUI.panel.FilelistPanel, OO.ui.PanelLayout );

ext.enhancedUI.panel.FilelistPanel.prototype.setupWidgets = function () {
	this.setupTools();

	mw.loader.using( this.pluginModules, () => {
		this.grid = new ext.enhancedUI.widget.FilelistGrid( {
			store: this.store,
			rights: this.rights,
			$overlay: this.$overlay,
			allowFileInfoDialog: this.allowFileInfoDialog,
			mediaDialog: this.mediaDialog
		} );
		this.grid.connect( this, {
			action: 'onGridAction',
			preview: 'onGridPreview'
		} );
		this.$element.append( this.grid.$element );
		this.emit( 'gridLoaded' );
	} );
};

ext.enhancedUI.panel.FilelistPanel.prototype.setupTools = function () {
	const toolsItems = [];
	this.input = new OO.ui.SearchInputWidget( {
		placeholder: mw.message( 'enhanced-standard-uis-filelist-panel-search-placeholder-label' ).text()
	} );

	this.typingTimer = null;
	this.typingDoneInterval = 200;
	this.input.connect( this, {
		change: 'onInputChange'
	} );

	if ( this.rights.indexOf( 'upload' ) !== -1 ) {
		this.uploadBtn = new OO.ui.ButtonWidget( {
			data: 'upload',
			label: mw.message( 'enhanced-standard-uis-filelist-panel-new-file-label' ).text(),
			icon: 'upload',
			classes: [ 'enhanced-filelist-upload-file' ],
			href: mw.util.getUrl( 'Special:Upload' ),
			invisibleLabel: true
		} );
		toolsItems.push( new OO.ui.FieldLayout( this.uploadBtn ) );
	}

	if ( this.canSwitchModes ) {
		this.typeSwitch = new OO.ui.ButtonSelectWidget( {
			items: [
				new OO.ui.ButtonOptionWidget( {
					data: 'tiles',
					label: mw.message( 'enhanced-standard-uis-filelist-panel-tiles-label' ).text(),
					title: mw.message( 'enhanced-standard-uis-filelist-panel-tiles-label' ).text(),
					icon: 'viewCompact'
				} ),
				new OO.ui.ButtonOptionWidget( {
					data: 'list',
					label: mw.message( 'enhanced-standard-uis-filelist-panel-list-label' ).text(),
					title: mw.message( 'enhanced-standard-uis-filelist-panel-list-label' ).text(),
					icon: 'listBullet'
				} )
			],
			classes: [ '' ]
		} );
		$( this.typeSwitch.$element ).attr( 'aria-label', 'Select view mode' );
		this.typeSwitch.selectItemByData( this.mode );
		this.typeSwitch.connect( this, {
			select: 'onTypeSwitchChange'
		} );

		toolsItems.push( new OO.ui.FieldLayout( this.typeSwitch, {
			classes: [ 'enhanced-filelist-tools-btn-select' ]
		} ) );
	}

	this.toolsLayout = new OO.ui.HorizontalLayout( {
		classes: [ 'enhanced-filelist-tools' ],
		items: [
			this.input,
			new OO.ui.HorizontalLayout( {
				classes: [ 'enhanced-filelist-tools-align-right' ],
				items: toolsItems
			} )
		]
	} );
	this.$element.append( this.toolsLayout.$element );
};

ext.enhancedUI.panel.FilelistPanel.prototype.onGridAction = function ( action, row ) {
	const data = {
		action: action,
		row: row
	};
	mw.hook( 'enhanced.filelist.action' ).fire( data );
	action = data.action;
	if ( action === 'info' ) {
		const windowManager = new OO.ui.WindowManager();
		$( document.body ).append( windowManager.$element );
		const infoDialog = new ext.enhancedUI.dialog.FileInfoDialog( {
			data: row,
			page: 'Data'
		} );
		windowManager.addWindows( [ infoDialog ] );
		windowManager.openWindow( infoDialog );
	}
	if ( action === 'reupload' ) {
		const reuploadUrl = mw.util.getUrl( 'Special:Upload',
			{
				wpDestFile: row.dbkey,
				wpForReUpload: 1
			}
		);
		window.location.href = reuploadUrl;
	}
	if ( action === 'delete' ) {
		const deleteUrl = mw.util.getUrl( 'File:' + row.dbkey,
			{
				action: 'delete'
			}
		);
		window.location.href = deleteUrl;
	}
};

ext.enhancedUI.panel.FilelistPanel.prototype.onGridPreview = function ( action, row ) {
	if ( !this.enablePreview ) {
		return;
	}
	const windowManager = new OO.ui.WindowManager();
	$( document.body ).append( windowManager.$element );
	const infoDialog = new ext.enhancedUI.dialog.FileInfoDialog( {
		data: row,
		page: 'Preview'
	} );
	windowManager.addWindows( [ infoDialog ] );
	windowManager.openWindow( infoDialog );
};

ext.enhancedUI.panel.FilelistPanel.prototype.onTypeSwitchChange = function ( selected ) {
	if ( !selected ) {
		return;
	}
	this.mode = selected.getData();
	if ( this.mode === 'tiles' ) {
		this.grid.setMode( 'tiles' );
	} else {
		this.grid.setMode( 'grid' );
	}
};

ext.enhancedUI.panel.FilelistPanel.prototype.onInputChange = function ( value ) {
	clearTimeout( this.typingTimer );
	if ( value === '' ) {
		this.store.clearQuery();
		this.store.reload();
		return;
	}

	this.typingTimer = setTimeout( () => {
		this.store.query( value );
	}, this.typingDoneInterval );
};

ext.enhancedUI.panel.FilelistPanel.prototype.closeFilters = function () {
	// Make sure to close all grid filter popups
	// Useful in case grid is getting hidden
	if ( !this.grid ) {
		return;
	}
	for ( const columnId in this.grid.columns ) {
		if ( this.grid.columns[ columnId ].filterButton ) {
			this.grid.columns[ columnId ].filterButton.getPopup().toggle( false );
		}
	}
};
