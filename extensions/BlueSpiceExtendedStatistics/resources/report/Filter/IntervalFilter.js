( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.IntervalFilter = function ( cfg ) {
		cfg = cfg || {};
		bs.aggregatedStatistics.filter.IntervalFilter.super.call( this, cfg );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.IntervalFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.IntervalFilter.prototype.init = function () {
		const intervalDays = new OO.ui.MenuOptionWidget( {
			data: 'day',
			label: mw.message( 'bs-statistics-aggregated-report-filter-interval-day' ).text()
		} );
		const intervalWeek = new OO.ui.MenuOptionWidget( {
			data: 'week',
			label: mw.message( 'bs-statistics-aggregated-report-filter-interval-week' ).text()
		} );
		const intervalMonth = new OO.ui.MenuOptionWidget( {
			data: 'month',
			label: mw.message( 'bs-statistics-aggregated-report-filter-interval-month' ).text()
		} );
		const intervalYear = new OO.ui.MenuOptionWidget( {
			data: 'year',
			label: mw.message( 'bs-statistics-aggregated-report-filter-interval-year' ).text()
		} );
		this.intervalWidget = new OO.ui.DropdownWidget( {
			menu: { items: [ intervalDays, intervalWeek, intervalMonth, intervalYear ] },
			classes: [ 'aggregatedStatistics-filter-field', 'interval-filter' ]
		} );

		this.intervalWidget.getMenu().selectItemByData( 'day' );

		this.intervalWidget.getMenu().on( 'select', this.onFilter.bind( this ) );

		this.$element.append( new OO.ui.FieldLayout( this.intervalWidget, {
			align: 'top',
			label: mw.message( 'bs-statistics-aggregated-report-filter-interval' ).text()
		} ).$element );
	};

	bs.aggregatedStatistics.filter.IntervalFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.IntervalFilter.prototype.getValue = function () {
		return {
			interval: this.intervalWidget.getMenu().findSelectedItem().getData()
		};
	};

	bs.aggregatedStatistics.filter.IntervalFilter.prototype.setValue = function ( value ) {
		if ( !value ) {
			return;
		}
		this.intervalWidget.getMenu().selectItemByData( value );
	};

}( mediaWiki, jQuery, blueSpice ) );
