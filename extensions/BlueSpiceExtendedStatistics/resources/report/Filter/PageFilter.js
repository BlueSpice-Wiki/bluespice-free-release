( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.PageFilter = function ( cfg ) {
		cfg = cfg || {};
		bs.aggregatedStatistics.filter.PageFilter.super.call( this, cfg );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.PageFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.PageFilter.prototype.init = function () {
		// We need multiselect widget
		this.pagePicker = new mw.widgets.TitleInputWidget( {
			classes: [ 'aggregatedStatistics-filter-field' ],
			required: this.required
		} );

		this.pagePicker.on( 'change', this.onFilter.bind( this ) );

		this.$element.append(
			new OO.ui.FieldLayout( this.pagePicker, {
				align: 'top',
				label: mw.message( 'bs-statistics-aggregated-report-filter-page' ).text()
			} ).$element
		);
	};

	bs.aggregatedStatistics.filter.PageFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.PageFilter.prototype.getValue = function () {
		return {
			page: this.pagePicker.getValue()
		};
	};

}( mediaWiki, jQuery, blueSpice ) );
