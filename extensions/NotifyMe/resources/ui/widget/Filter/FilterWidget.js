ext.notifyme.ui.widget.FilterWidget = function ( cfg ) {
	cfg = cfg || {};

	this.mobileView = cfg.mobileView || false;
	ext.notifyme.ui.widget.FilterWidget.super.call( this, cfg );

	this.filterData = {};
	this.activeFilter = 'title-all';

	this.$element.addClass( 'notifications-ui-widget-FilterWidget' );
};

OO.inheritClass( ext.notifyme.ui.widget.FilterWidget, OO.ui.Widget );

ext.notifyme.ui.widget.FilterWidget.prototype.loadData = function ( filterData, activeFilter ) {
	this.filterData = filterData;
	this.activeFilter = activeFilter;

	if ( this.mobileView ) {
		this.updateMobileContent();
	} else {
		this.updateContent();
	}
};

ext.notifyme.ui.widget.FilterWidget.prototype.updateContent = function () {
	const options = [];
	for ( const filter in this.filterData ) {
		if ( this.filterData[ filter ].items.length === 0 ) {
			continue;
		}
		const filterType = this.filterData[ filter ].type;
		if ( filterType !== 'title' ) {
			options.push( { optgroup: this.filterData[ filter ].label } );
		}
		for ( const item in this.filterData[ filter ].items ) {
			const filterKey = filterType + '-' + this.filterData[ filter ].items[ item ].key;
			const count = this.filterData[ filter ].items[ item ].count;
			options.push( {
				data: filterKey,
				label: this.filterData[ filter ].items[ item ].label,
				batch: count,
				batchText: mw.message( 'notifyme-notification-filter-count-aria-label', count ).text()
			} );
		}
	}
	if ( !this.selectWidget ) {
		this.selectWidget = new OOJSPlus.ui.widget.GroupedSelectWidget( {
			options: options
		} );
		this.selectWidget.getMenu().$element.attr( 'aria-label',
			mw.message( 'notifyme-notification-filter-aria-label' ).text() );
		this.selectWidget.connect( this, {
			select: 'filterSelected'
		} );
		this.$element.append( this.selectWidget.$element );
	}
	this.selectWidget.getMenu().selectItemByData( this.activeFilter );
};

ext.notifyme.ui.widget.FilterWidget.prototype.updateMobileContent = function () {
	const options = [];
	for ( const filter in this.filterData ) {
		if ( this.filterData[ filter ].items.length === 0 ) {
			continue;
		}
		const filterType = this.filterData[ filter ].type;
		if ( filterType !== 'title' ) {
			options.push( new OO.ui.MenuSectionOptionWidget( {
				label: this.filterData[ filter ].label
			} ) );
		}
		for ( const item in this.filterData[ filter ].items ) {
			const filterKey = filterType + '-' + this.filterData[ filter ].items[ item ].key;
			options.push( new OO.ui.MenuOptionWidget( {
				data: filterKey,
				label: this.filterData[ filter ].items[ item ].label
			} ) );
		}
	}
	if ( !this.selectWidget ) {
		this.selectWidget = new OO.ui.DropdownWidget( {
			menu: {
				items: options
			}
		} );
		this.selectWidget.getMenu().connect( this, {
			select: 'filterSelected'
		} );
		this.$element.append( this.selectWidget.$element );
	}
	this.selectWidget.getMenu().selectItemByData( this.activeFilter );
};

ext.notifyme.ui.widget.FilterWidget.prototype.filterSelected = function () {
	const nsOption = this.selectWidget.getMenu().findSelectedItem();

	this.emit( 'selectItem', nsOption.data );
};
