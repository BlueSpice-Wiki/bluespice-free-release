ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.panel = ext.enhancedUI.panel || {};

require( '../widget/HistoryToolbar.js' );
require( '../widget/HistoryGrid.js' );

ext.enhancedUI.panel.HistoryPanel = function ( cfg ) {
	ext.enhancedUI.panel.HistoryPanel.super.apply( this, cfg );
	const defaultModeConfig = require( './viewmode.json' );
	this.defaultMode = defaultModeConfig.mode;
	this.selectedElements = [];
	this.rights = cfg.rights || [];
	this.historyData = cfg.data || [];

	this.data = [];
	this.$element = $( '<div>' );
	this.setup();
};

OO.inheritClass( ext.enhancedUI.panel.HistoryPanel, OO.ui.PanelLayout );

ext.enhancedUI.panel.HistoryPanel.prototype.setup = function () {
	this.setupToolbar();
	this.setupGrid();
};

ext.enhancedUI.panel.HistoryPanel.prototype.setupToolbar = function () {
	this.historyToolbar = new ext.enhancedUI.widget.HistoryToolbar();
	this.historyToolbar.connect( this, {
		compare: function () {
			const compareValues = this.getCompareValues();
			if ( !compareValues ) {
				return;
			}
			const url = mw.util.getUrl( mw.config.get( 'wgPageName' ),
				{
					type: 'revision',
					diff: compareValues.diff,
					oldid: compareValues.oldid,
					diffmode: this.defaultMode
				}
			);
			window.location.href = url;
		}
	} );
	this.toggleCompareButton( true );
	this.$element.append( this.historyToolbar.$element );
};

ext.enhancedUI.panel.HistoryPanel.prototype.getCompareValues = function () {
	const selectedElements = this.grid.getSelectedRows();
	let diffValue = parseInt( selectedElements[ 0 ].id );
	let oldIdValue = parseInt( selectedElements[ 1 ].id );

	if ( isNaN( diffValue ) || isNaN( oldIdValue ) ) {
		return null;
	}

	if ( diffValue < oldIdValue ) {
		const switchValue = diffValue;
		diffValue = oldIdValue;
		oldIdValue = switchValue;
	}
	return {
		diff: diffValue,
		oldid: oldIdValue
	};
};

ext.enhancedUI.panel.HistoryPanel.prototype.setupGrid = function () {
	const gridCfg = this.setupGridConfig();
	this.grid = new ext.enhancedUI.widget.HistoryGrid( gridCfg );
	this.grid.connect( this, {
		action: 'doActionOnRow',
		rowSelected: 'rowSelect'
	} );

	// ERM35472 - hide reload button on history panel
	const reloadItem = this.grid.toolbar.staticControls.items[ 1 ];
	if ( reloadItem.icon === 'reload' ) {
		this.grid.toolbar.staticControls.removeItems( [ reloadItem ] );
	}

	for ( let i = 1; i < $( this.grid.$table ).children().length; i++ ) {
		const $row = $( this.grid.$table ).children()[ i ];
		// First entry of grid is table header, so grid and data is not aligned with selector
		const classSelector = i - 1;
		/* eslint-disable-next-line no-jquery/no-class-state */
		if ( $( $row ).hasClass( 'oojsplus-data-gridWidget-row' ) &&
			this.historyData[ classSelector ].classes.length > 0 ) {

			this.historyData[ classSelector ].classes.forEach( ( rowClass ) => {
				/* eslint-disable-next-line mediawiki/class-doc */
				$( $row ).addClass( rowClass );
			} );
		}
	}

	this.$element.append( this.grid.$element );
};

ext.enhancedUI.panel.HistoryPanel.prototype.doActionOnRow = function ( action, row ) {
	if ( action === 'undo' ) {
		let undoAfterIndex = 0;
		for ( let i = 0; i < this.historyData.length; i++ ) {
			if ( this.historyData[ i ].id !== row.id ) {
				continue;
			}
			undoAfterIndex = i + 1;
			break;
		}
		const undoUrl = mw.util.getUrl( mw.config.get( 'wgPageName' ),
			{
				action: 'edit',
				undoafter: this.historyData[ undoAfterIndex ].id,
				undo: row.id
			}
		);
		window.location.href = undoUrl;
	}
	if ( action === 'hide' ) {
		let hideUrl = mw.util.getUrl( mw.config.get( 'wgPageName' ),
			{
				action: 'revisiondelete',
				type: 'revision'
			}
		);
		hideUrl += '&ids[' + row.id + ']=1';
		window.location.href = hideUrl;
	}

	mw.hook( 'enhanced.versionhistory.rowAction' ).fire( action, row );
};

ext.enhancedUI.panel.HistoryPanel.prototype.rowSelect = function () {
	const limitReached = this.grid.selectionLimitReached();
	this.toggleCompareButton( !limitReached );
};

ext.enhancedUI.panel.HistoryPanel.prototype.setupGridConfig = function () {
	const gridCfg = {
		style: 'differentiate-rows',
		multiSelect: true,
		exportable: false,
		columns: {
			revision: {
				headerText: mw.message( 'enhanced-standard-uis-history-grid-header-revision-label' ).text(),
				type: 'url',
				sortable: false,
				urlProperty: 'revisionUrl',
				hidden: !mw.user.options.get( 'history-show-revision' )
			},
			author: {
				headerText: mw.message( 'enhanced-standard-uis-history-grid-header-author-label' ).text(),
				type: 'user',
				showImage: true,
				sortable: false,
				hidden: !mw.user.options.get( 'history-show-author' )
			},
			diff: {
				headerText: mw.message( 'enhanced-standard-uis-history-grid-header-diff-label' ).text(),
				type: 'text',
				sortable: false,
				hidden: !mw.user.options.get( 'history-show-diff' )
			},
			size: {
				headerText: mw.message( 'enhanced-standard-uis-history-grid-header-size-label' ).text(),
				type: 'text',
				sortable: false,
				hidden: !mw.user.options.get( 'history-show-size' )
			},
			summary: {
				headerText: mw.message( 'enhanced-standard-uis-history-grid-header-summary-label' ).text(),
				type: 'text',
				sortable: false,
				maxLabelLength: 100,
				hidden: !mw.user.options.get( 'history-show-summary' ),
				width: 300,
				valueParser: ( value ) => new OO.ui.HtmlSnippet( value )
			},
			tags: {
				headerText: mw.message( 'enhanced-standard-uis-history-grid-header-tags-label' ).text(),
				type: 'url',
				sortable: false,
				urlProperty: 'tagUrl',
				hidden: !mw.user.options.get( 'history-show-tags' )
			}
		},
		data: this.historyData
	};

	mw.hook( 'enhanced.versionhistory' ).fire( gridCfg );

	if ( this.rights.indexOf( 'rollback' ) !== -1 ) {
		gridCfg.columns.undo = {
			type: 'action',
			title: mw.message( 'enhanced-standard-uis-history-grid-header-undo-title' ).text(),
			actionId: 'undo',
			icon: 'undo'
		};
	}
	if ( this.rights.indexOf( 'deleterevision' ) !== -1 ) {
		gridCfg.columns.hide = {
			type: 'action',
			title: mw.message( 'enhanced-standard-uis-history-grid-header-hide-revision-title' ).text(),
			actionId: 'hide',
			icon: 'trash'
		};
	}

	return gridCfg;
};

ext.enhancedUI.panel.HistoryPanel.prototype.toggleCompareButton = function ( disable ) {
	const compareTool = this.historyToolbar.getToolGroupByName( 'compare-action' ).findItemFromData( 'compare' );
	compareTool.setDisabled( disable );
};
