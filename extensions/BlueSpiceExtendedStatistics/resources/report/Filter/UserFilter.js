( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.UserFilter = function ( cfg ) {
		cfg = cfg || {};
		bs.aggregatedStatistics.filter.UserFilter.super.call( this, cfg );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.UserFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.UserFilter.prototype.init = function () {
		this.userPicker = new mw.widgets.UserInputWidget( {
			classes: [ 'aggregatedStatistics-filter-field' ],
			required: this.required
		} );

		this.userPicker.on( 'change', this.onFilter.bind( this ) );

		this.$element.append(
			new OO.ui.FieldLayout( this.userPicker, {
				align: 'top',
				label: mw.message( 'bs-statistics-aggregated-report-filter-user' ).text()
			} ).$element
		);
	};

	bs.aggregatedStatistics.filter.UserFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.UserFilter.prototype.getValue = function () {
		return {
			user: this.userPicker.getValue()
		};
	};

}( mediaWiki, jQuery, blueSpice ) );
