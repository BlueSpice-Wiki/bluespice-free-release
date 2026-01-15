( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.UserMultiFilter = function ( cfg ) {
		cfg = cfg || {};
		bs.aggregatedStatistics.filter.UserMultiFilter.super.call( this, cfg );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.UserMultiFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.UserMultiFilter.prototype.init = function () {
		this.userPicker = new mw.widgets.UsersMultiselectWidget( {
			classes: [ 'aggregatedStatistics-filter-field' ],
			indicator: this.required ? 'required' : null
		} );

		this.userPicker.on( 'change', this.onFilter.bind( this ) );

		this.$element.append(
			new OO.ui.FieldLayout( this.userPicker, {
				align: 'top',
				label: mw.message( 'bs-statistics-aggregated-report-filter-user-multi' ).text()
			} ).$element
		);
	};

	bs.aggregatedStatistics.filter.UserMultiFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.UserMultiFilter.prototype.getValue = function () {
		return {
			users: this.userPicker.getValue()
		};
	};

}( mediaWiki, jQuery, blueSpice ) );
