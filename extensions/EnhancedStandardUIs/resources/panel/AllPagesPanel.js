ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.panel = ext.enhancedUI.panel || {};

require( '../widget/Paginator.js' );
require( '../widget/NamespacesMenu.js' );
require( '../data/PagesTree.js' );
require( '../data/store/Store.js' );

ext.enhancedUI.panel.AllPagesPanel = function ( cfg ) {
	ext.enhancedUI.panel.AllPagesPanel.super.apply( this, cfg );
	this.mobileView = cfg.mobileView;
	this.selectedNamespaceId = cfg.namespaceId;
	this.pageSize = 50;
	this.store = new ext.enhancedUI.data.store.Store();
	this.$element = $( '<div>' ).addClass( 'enhanced-ui-allpages-panel' );

	this.searchWidget = OO.ui.infuse( '#enhanced-ui-allpages-filter' );
	this.searchWidget.connect( this, {
		change: 'onFilterInput'
	} );
	this.searchWidget.$indicator.attr( 'tabindex', 0 );

	this.setupWidgets();
};

OO.inheritClass( ext.enhancedUI.panel.AllPagesPanel, OO.ui.PanelLayout );

ext.enhancedUI.panel.AllPagesPanel.prototype.setupWidgets = function () {
	this.setupMenu();
	this.$contentCnt = $( '<div>' ).addClass( 'enhanced-ui-allpages-panel-content' );
	this.setupPaginator();
	this.setupTree();
	this.$resultCounter = $( '<div>' ).attr( 'aria-live', 'polite' ).addClass( 'visually-hidden' );
	this.$contentCnt.append( this.$resultCounter );
	this.$element.append( this.$contentCnt );
};

ext.enhancedUI.panel.AllPagesPanel.prototype.setupMenu = function () {
	const $menuCnt = $( '<div>' ).addClass( 'enhanced-ui-allpages-panel-menu' );
	// eslint-disable-next-line no-jquery/no-global-selector
	this.$menuPlaceholder = $( '#skeleton-namespaces' ).clone();
	$menuCnt.append( this.$menuPlaceholder );
	this.$menuPlaceholder.attr( 'id', 'enhanced-allpages-skeleton-namespaces' );
	// eslint-disable-next-line no-jquery/no-global-selector
	$( '#skeleton-namespaces' ).empty();
	if ( this.mobileView ) {
		$menuCnt.addClass( 'collapsed' );
		$menuCnt.addClass( 'oo-ui-icon-next' );
		$( $menuCnt ).on( 'click', function () {
			// eslint-disable-next-line no-jquery/no-class-state
			if ( $( this ).hasClass( 'collapsed' ) ) {
				$( this ).removeClass( 'collapsed' );
				$( this ).removeClass( 'oo-ui-icon-next' );
			} else {
				$( this ).addClass( 'collapsed' );
				$( this ).addClass( 'oo-ui-icon-next' );
			}
		} );
	}
	this.namespaceMenu = new ext.enhancedUI.widget.NamespacesMenu( {
		selectedNSId: this.selectedNamespaceId
	} );
	this.namespaceMenu.connect( this, {
		select: 'namespaceSelected',
		redirectChange: 'updateRedirect',
		setup: function () {
			this.selectedNS = this.namespaceMenu.getSelectedNamespaceId();
			this.includeRedirect = this.namespaceMenu.getRedirectStatus();
			this.$menuPlaceholder.empty();
		}
	} );
	$menuCnt.append( this.namespaceMenu.$element );
	this.$element.append( $menuCnt );
};

ext.enhancedUI.panel.AllPagesPanel.prototype.setupPaginator = function () {
	const $paginatorCnt = $( '<div>' ).addClass( 'enhanced-ui-allpages-panel-paginator' );
	this.paginator = new ext.enhancedUI.widget.Paginator();
	this.paginator.connect( this, {
		selectPage: function ( nextPage ) {
			this.changeFromPaginator = true;
			this.getPages( nextPage * this.pageSize );
		}
	} );
	$paginatorCnt.append( this.paginator.$element );
	this.$contentCnt.append( $paginatorCnt );
};

ext.enhancedUI.panel.AllPagesPanel.prototype.setupTree = function () {
	this.$outerTreeCnt = $( '<div>' ).addClass(
		'enhanced-ui-allpages-panel-tree-cnt' );
	this.$treeCnt = $( '<div>' ).addClass(
		'enhanced-ui-allpages-panel-tree enhanced-ui-allpages-columns' );

	// eslint-disable-next-line no-jquery/no-global-selector
	this.$treePlaceholder = $( '#skeleton-tree' ).clone();
	this.$outerTreeCnt.append( this.$treePlaceholder );
	this.$treePlaceholder.attr( 'id', 'enhanced-allpages-skeleton-tree' );
	// eslint-disable-next-line no-jquery/no-global-selector
	$( '#skeleton-tree' ).empty();

	this.$outerTreeCnt.append( this.$treeCnt );
	this.$contentCnt.append( this.$outerTreeCnt );
};

ext.enhancedUI.panel.AllPagesPanel.prototype.namespaceSelected = function ( nsId ) {
	if ( this.selectedNS === nsId ) {
		return;
	}
	this.selectedNS = nsId;
	this.getPages();
};

ext.enhancedUI.panel.AllPagesPanel.prototype.updateRedirect = function ( redirect ) {
	if ( this.includeRedirect === redirect ) {
		return;
	}
	this.includeRedirect = redirect;
	this.getPages();
};

ext.enhancedUI.panel.AllPagesPanel.prototype.getPages = function ( start ) {
	start = start || 0;
	this.store.loadNS( this.selectedNS, start ).done( ( data ) => {
		const sortedData = this.sortData( data );
		this.pages = sortedData;
		if ( this.changeFromPaginator !== true ) {
			this.paginator.init( Math.ceil( this.store.getTotal() / this.pageSize ) );
		}
		this.changeFromPaginator = false;
		this.updatePages();
	} );
};

ext.enhancedUI.panel.AllPagesPanel.prototype.updatePages = function () {
	this.$treeCnt.children().remove();
	if ( this.pages.length > 1 ) {
		// eslint-disable-next-line no-jquery/no-class-state
		if ( !$( this.$treeCnt ).hasClass( 'enhanced-ui-allpages-columns' ) ) {
			$( this.$treeCnt ).addClass( 'enhanced-ui-allpages-columns' );
		}
	} else {
		// eslint-disable-next-line no-jquery/no-class-state
		if ( $( this.$treeCnt ).hasClass( 'enhanced-ui-allpages-columns' ) ) {
			$( this.$treeCnt ).removeClass( 'enhanced-ui-allpages-columns' );
		}
	}
	this.$treePlaceholder.empty();
	if ( this.pages.length === 0 ) {
		this.$treeCnt.append(
			new OOJSPlus.ui.widget.NoContentPlaceholderWidget( {
				icon: 'allpages-no-page',
				classes: [ 'allpages-empty-pages-cnt' ],
				label: mw.message( 'enhanced-standard-uis-allpages-empty-pages-text' ).text()
			} ).$element
		);
		return;
	}
	for ( const i in this.pages ) {
		// eslint-disable-next-line no-jquery/variable-pattern
		const pageTreeLetter = $( '<h2>' ).text( this.alphabetIndex[ i ] );
		const pageTree = new ext.enhancedUI.data.PagesTree( {
			style: {
				IconExpand: 'next',
				IconCollapse: 'expand'
			},
			pages: this.pages[ i ],
			store: this.store,
			includeRedirect: this.includeRedirect
		} );
		this.$treeCnt.append( pageTreeLetter );
		this.$treeCnt.append( pageTree.$element );
	}
};

ext.enhancedUI.panel.AllPagesPanel.prototype.onFilterInput = function () {
	this.searchWidget.$input.addClass( 'oo-ui-pendingElement-pending' );
	const searchString = this.searchWidget.getValue();
	this.store.loadPages( this.selectedNS, searchString ).done( ( data ) => {
		const sortedData = this.sortData( data );
		this.pages = sortedData;
		this.updateResults( data );
		this.paginator.init( Math.ceil( this.store.getTotal() / this.pageSize ) );
		this.updatePages();
		this.searchWidget.$input.removeClass( 'oo-ui-pendingElement-pending' );
	} );
};

ext.enhancedUI.panel.AllPagesPanel.prototype.updateResults = function ( data ) {
	const resultNumber = this.calculateResultNumber( data, 0 );
	this.$resultCounter.text(
		mw.message( 'enhanced-standard-uis-allpages-filter-results-label', resultNumber ).text()
	);
};

ext.enhancedUI.panel.AllPagesPanel.prototype.calculateResultNumber = function ( items, resultNumber ) {
	for ( const i in items ) {
		if ( !items[ i ].expanded ) {
			resultNumber += 1;
			continue;
		}
		for ( const j in items[ i ].children ) {
			if ( !items[ i ].children[ j ].expanded ) {
				resultNumber += 1;
				continue;
			}
			return this.calculateResultNumber( [ items[ i ].children[ j ] ], resultNumber );
		}
	}
	return resultNumber;
};

ext.enhancedUI.panel.AllPagesPanel.prototype.sortData = function ( data ) {
	this.alphabetIndex = [];
	if ( Object.keys( data ).length === 0 ) {
		return [];
	}
	let alphabetValue = [];
	const sortedData = [];
	let lastLetter = data[ 0 ].dbkey.slice( 0, 1 );
	for ( const i in data ) {
		if ( !this.includeRedirect && data[ i ].redirect === true ) {
			continue;
		}
		const startLetter = data[ i ].dbkey.slice( 0, 1 );
		if ( lastLetter !== startLetter ) {
			sortedData.push( alphabetValue );
			alphabetValue = [];
		}
		if ( this.alphabetIndex.indexOf( startLetter ) === -1 ) {
			this.alphabetIndex.push( startLetter );
		}
		alphabetValue.push( data[ i ] );
		lastLetter = startLetter;
	}
	sortedData.push( alphabetValue );
	return sortedData;
};
