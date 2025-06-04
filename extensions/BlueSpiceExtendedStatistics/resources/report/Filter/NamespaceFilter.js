( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.NamespaceFilter = function ( cfg ) {
		cfg = cfg || {};
		this.onlyContentNamespaces = cfg.onlyContentNamespaces || false;
		bs.aggregatedStatistics.filter.NamespaceFilter.super.call( this, cfg );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.NamespaceFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.NamespaceFilter.prototype.init = function () {
		this.namespaceSelect = new OO.ui.DropdownInputWidget( {
			options: this.getOptions( true ),
			classes: [ 'aggregatedStatistics-filter-field' ]
		} );

		this.namespaceSelect.on( 'change', this.onFilter.bind( this ) );

		const namespaceLayout = new OO.ui.FieldLayout( this.namespaceSelect, {
			label: mw.message( 'bs-statistics-aggregated-report-filter-namespace' ).text(),
			align: 'top'
		} );
		this.$element.append( namespaceLayout.$element );
	};

	bs.aggregatedStatistics.filter.NamespaceFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.NamespaceFilter.prototype.getOptions = function ( includeNoValue ) {
		let ids = this.onlyContentNamespaces ?
			mw.config.get( 'wgContentNamespaces' ) : Object.values( mw.config.get( 'wgNamespaceIds' ) );
		ids = ids.filter( ( value, index, self ) => value >= 0 && self.indexOf( value ) === index );
		const names = mw.config.get( 'wgFormattedNamespaces' );
		const namespaces = [];
		if ( includeNoValue ) {
			namespaces.push( {
				data: '',
				label: '-'
			} );
		}
		for ( let i = 0; i < ids.length; i++ ) {
			if ( ids[ i ] === 0 ) {
				namespaces.push( { data: '-', label: mw.message( 'bs-ns_main' ).text() } );
			} else if ( names.hasOwnProperty( ids[ i ] ) ) {
				namespaces.push( { data: ids[ i ], label: names[ ids[ i ] ] } );
			}
		}

		return namespaces;
	};

	bs.aggregatedStatistics.filter.NamespaceFilter.prototype.getValue = function () {
		return {
			namespace: this.namespaceSelect.getValue()
		};
	};

	bs.aggregatedStatistics.filter.NamespaceFilter.prototype.clear = function () {
		this.namespaceSelect.setValue( '' );
	};

}( mediaWiki, jQuery, blueSpice ) );
