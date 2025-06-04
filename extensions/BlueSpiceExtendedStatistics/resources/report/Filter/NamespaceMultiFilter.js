( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.NamespaceMultiFilter = function ( cfg ) {
		cfg = cfg || {};
		this.onlyContentNamespaces = cfg.onlyContentNamespaces || false;
		bs.aggregatedStatistics.filter.NamespaceMultiFilter.super.call( this, cfg );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.NamespaceMultiFilter, bs.aggregatedStatistics.filter.NamespaceFilter );

	bs.aggregatedStatistics.filter.NamespaceMultiFilter.prototype.init = function () {
		this.namespaceSelect = new OO.ui.MenuTagMultiselectWidget( {
			allowArbitrary: false,
			options: this.getOptions(),
			classes: [ 'aggregatedStatistics-filter-field' ]
		} );

		this.namespaceSelect.on( 'change', this.onFilter.bind( this ) );

		const namespaceLayout = new OO.ui.FieldLayout( this.namespaceSelect, {
			label: mw.message( 'bs-statistics-aggregated-report-filter-namespaces' ).text(),
			align: 'top'
		} );
		this.$element.append( namespaceLayout.$element );
	};

	bs.aggregatedStatistics.filter.NamespaceMultiFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.NamespaceMultiFilter.prototype.getValue = function () {
		return {
			namespaces: this.namespaceSelect.getValue()
		};
	};

	bs.aggregatedStatistics.filter.NamespaceMultiFilter.prototype.clear = function () {
		this.namespaceSelect.setValue( [] );
	};

}( mediaWiki, jQuery, blueSpice ) );
