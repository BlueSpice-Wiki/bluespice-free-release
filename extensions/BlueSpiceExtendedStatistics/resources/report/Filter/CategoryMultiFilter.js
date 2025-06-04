( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.CategoryMultiFilter = function ( cfg ) {
		cfg = cfg || {};
		bs.aggregatedStatistics.filter.CategoryMultiFilter.super.call( this, cfg );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.CategoryMultiFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.CategoryMultiFilter.prototype.init = function () {
		this.categoryPicker = new OOJSPlus.ui.widget.CategoryMultiSelectWidget( {
			classes: [ 'aggregatedStatistics-filter-field' ],
			indicator: this.required ? 'required' : null
		} );

		this.categoryPicker.on( 'change', this.onFilter.bind( this ) );

		this.$element.append(
			new OO.ui.FieldLayout( this.categoryPicker, {
				align: 'top',
				label: mw.message( 'bs-statistics-aggregated-report-filter-category-multi' ).text()
			} ).$element
		);
	};

	bs.aggregatedStatistics.filter.CategoryMultiFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.CategoryMultiFilter.prototype.getValue = function () {
		return {
			categories: this.categoryPicker.getValue()
		};
	};

	bs.aggregatedStatistics.filter.CategoryMultiFilter.prototype.clear = function () {
		this.categoryPicker.setValue( [] );
	};

}( mediaWiki, jQuery, blueSpice ) );
