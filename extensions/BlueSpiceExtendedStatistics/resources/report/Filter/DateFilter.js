( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.DateFilter = function ( cfg ) {
		cfg = cfg || {};
		bs.aggregatedStatistics.filter.DateFilter.super.call( this, cfg );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.DateFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.DateFilter.prototype.init = function () {
		this.datePickerStart = new mw.widgets.DateInputWidget();
		this.datePickerEnd = new mw.widgets.DateInputWidget();
		this.datePickerStart.connect( this, {
			change: 'onFilter'
		} );
		this.datePickerEnd.connect( this, {
			change: 'onFilter'
		} );

		const datePickerLayout = new OO.ui.HorizontalLayout( {
			items: [
				new OO.ui.FieldLayout( this.datePickerStart, {
					label: mw.message( 'bs-statistics-aggregated-report-filter-date-start' ).text(),
					align: 'top'
				} ),
				new OO.ui.FieldLayout( this.datePickerEnd, {
					label: mw.message( 'bs-statistics-aggregated-report-filter-date-end' ).text(),
					align: 'top'
				} )
			]
		} );
		this.$element.append( datePickerLayout.$element );
	};

	bs.aggregatedStatistics.filter.DateFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.DateFilter.prototype.getValue = function () {
		if ( !this.datePickerEnd || !this.datePickerStart ) {
			return {};
		}

		return {
			date: {
				dateStart: this.datePickerStart.getValue(),
				dateEnd: this.datePickerEnd.getValue()
			}
		};
	};

	bs.aggregatedStatistics.filter.DateFilter.prototype.setValue = function ( value ) {
		if ( !value ) {
			return;
		}
		this.datePickerStart.setValue( value.dateStart || '' );
		this.datePickerEnd.setValue( value.dateEnd || '' );
	};

	bs.aggregatedStatistics.filter.DateFilter.prototype.getName = function () {
		return 'date';
	};
}( mediaWiki, jQuery, blueSpice ) );
