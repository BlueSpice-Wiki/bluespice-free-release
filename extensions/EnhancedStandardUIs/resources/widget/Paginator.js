ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.widget = ext.enhancedUI.widget || {};

ext.enhancedUI.widget.Paginator = function ( cfg ) {
	cfg = cfg || {};
	ext.enhancedUI.widget.Paginator.parent.call( this, cfg );

	this.pageSize = cfg.pageSize || 50;
	this.total = 0;
	this.loaded = 0;
	this.rows = {};
	this.range = { start: 0, end: 0 };
	this.hasPages = false;
	this.currentPage = 0;
	this.splitPageButtons = false;
	this.splitBreakPoint = cfg.splitBreakPoint || 5;
	this.navigation = new OO.ui.HorizontalLayout();
	this.$element.addClass( 'enhancedUI-paginator' );
	this.$element.attr( 'aria-label',
		mw.message( 'enhanced-standard-uis-paginator-aria-label' ).text() );
	this.$element.append( this.navigation.$element );

	this.groupPaginator = new OO.ui.ButtonGroupWidget();
};

OO.inheritClass( ext.enhancedUI.widget.Paginator, OO.ui.Widget );

ext.enhancedUI.widget.Paginator.static.tagName = 'nav';

ext.enhancedUI.widget.Paginator.prototype.init = function ( total ) {
	this.total = total;
	this.numberOfPages = total - 1;
	this.clear();
	this.createControls();
	this.updateControls();
};

ext.enhancedUI.widget.Paginator.prototype.createControls = function () {
	this.createBackButton();
	this.createPageButtons();
	this.createForwardButton();

	this.navigation.addItems( [
		this.groupPaginator
	] );
};

ext.enhancedUI.widget.Paginator.prototype.createBackButton = function () {
	this.firstButton = new OO.ui.ButtonWidget( {
		icon: 'doubleChevronStart',
		invisibleLabel: true,
		label: mw.message( 'enhanced-standard-uis-allpages-paginator-first' ).text()
	} );
	this.firstButton.connect( this, {
		click: 'first'
	} );

	this.previousButton = new OO.ui.ButtonWidget( {
		icon: 'previous',
		invisibleLabel: true,
		label: mw.message( 'enhanced-standard-uis-allpages-paginator-previous' ).text()
	} );
	this.previousButton.connect( this, {
		click: 'previous'
	} );

	this.groupPaginator.addItems( [
		this.firstButton,
		this.previousButton
	] );
};

ext.enhancedUI.widget.Paginator.prototype.createPageButtons = function () {
	const buttons = [];
	if ( this.numberOfPages > 8 ) {
		this.splitPageButtons = true;
	}
	for ( let i = 0; i <= this.numberOfPages; i++ ) {
		const button = new OO.ui.ButtonOptionWidget( {
			label: ( i + 1 ).toString(),
			data: i
		} );
		if ( this.splitPageButtons && i > this.splitBreakPoint ) {
			button.toggle( false );
		}
		buttons.push( button );
	}
	this.buttonSelect = new OO.ui.ButtonSelectWidget( {
		items: buttons
	} );
	this.buttonSelect.$element.attr( 'aria-label',
		mw.message( '' ).text() );
	this.buttonSelect.selectItem( this.buttonSelect.findFirstSelectableItem() );
	this.buttonSelect.connect( this, {
		select: 'selectNumberButton'
	} );

	this.groupPaginator.addItems( [
		this.buttonSelect
	] );
};

ext.enhancedUI.widget.Paginator.prototype.createForwardButton = function () {
	this.nextButton = new OO.ui.ButtonWidget( {
		icon: 'next',
		invisibleLabel: true,
		label: mw.message( 'enhanced-standard-uis-allpages-paginator-next' ).text()
	} );
	this.nextButton.connect( this, {
		click: 'next'
	} );
	this.lastButton = new OO.ui.ButtonWidget( {
		icon: 'doubleChevronEnd',
		invisibleLabel: true,
		label: mw.message( 'enhanced-standard-uis-allpages-paginator-last' ).text()
	} );
	this.lastButton.connect( this, {
		click: 'last'
	} );

	this.groupPaginator.addItems( [
		this.nextButton,
		this.lastButton
	] );
};

ext.enhancedUI.widget.Paginator.prototype.clear = function () {
	this.navigation.$element.children().remove();
	this.groupPaginator.clearItems();
};

ext.enhancedUI.widget.Paginator.prototype.selectNumberButton = function ( item ) {
	if ( this.currentPage === item.data ) {
		return;
	}
	this.currentPage = item.data;
	this.updateControls();
	this.emit( 'selectPage', this.currentPage );
};

ext.enhancedUI.widget.Paginator.prototype.updateControls = function () {
	this.previousButton.setDisabled( this.currentPage === 0 );
	this.firstButton.setDisabled( this.currentPage === 0 );
	this.nextButton.setDisabled( this.currentPage === this.numberOfPages );
	this.lastButton.setDisabled( this.currentPage === this.numberOfPages );

	if ( !this.splitPageButtons ) {
		return;
	}
	let minRange = this.currentPage - 2;
	let maxRange = this.currentPage + 2;
	if ( minRange < 0 ) {
		minRange = 0;
		maxRange = this.currentPage + 4;
	}
	if ( maxRange > this.numberOfPages ) {
		maxRange = this.numberOfPages;
		minRange = this.currentPage - 4;
	}

	this.buttonSelect.items.forEach( ( button ) => {
		if ( minRange <= button.data && button.data <= maxRange ) {
			button.toggle( true );
		} else {
			if ( button.isVisible() ) {
				button.toggle( false );
			}
		}
	} );
};

ext.enhancedUI.widget.Paginator.prototype.first = function () {
	this.buttonSelect.selectItemByData( 0 );
};

ext.enhancedUI.widget.Paginator.prototype.previous = function () {
	const data = this.currentPage - 1;
	this.buttonSelect.selectItemByData( data );
};

ext.enhancedUI.widget.Paginator.prototype.next = function () {
	const data = this.currentPage + 1;
	this.buttonSelect.selectItemByData( data );
};

ext.enhancedUI.widget.Paginator.prototype.last = function () {
	this.buttonSelect.selectItemByData( this.numberOfPages );
};
