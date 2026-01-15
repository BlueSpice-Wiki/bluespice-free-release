( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.NamespaceCategoryFilter = function ( cfg ) {
		cfg = cfg || {};
		this.listenForChanges = true;
		bs.aggregatedStatistics.filter.NamespaceCategoryFilter.super.call( this, cfg );

		this.nsFilter = new bs.aggregatedStatistics.filter.NamespaceMultiFilter( cfg );
		this.categoryFilter = new bs.aggregatedStatistics.filter.CategoryMultiFilter( cfg );

		this.categoryFilter.on( 'change', this.onCategoryChange.bind( this ) );
		this.nsFilter.on( 'change', this.onNamespaceChange.bind( this ) );

		this.$element.append( new OO.ui.LabelWidget( {
			label: mw.message( 'bs-statistics-aggregated-filter-category-namespace-notice' ).text()
		} ).$element );
		this.$element.append( new OO.ui.HorizontalLayout( { items: [
			this.categoryFilter, this.nsFilter
		] } ).$element );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.NamespaceCategoryFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.NamespaceCategoryFilter.prototype.init = function () {};

	bs.aggregatedStatistics.filter.NamespaceCategoryFilter.prototype.onFilter = function () {
		if ( !this.listenForChanges ) {
			return;
		}
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.NamespaceCategoryFilter.prototype.onNamespaceChange = function () {
		if ( !this.listenForChanges ) {
			return;
		}
		if ( this.nsFilter.getValue() ) {
			this.listenForChanges = false;
			this.categoryFilter.clear();
			this.listenForChanges = true;
		}
		this.onFilter();
	};

	bs.aggregatedStatistics.filter.NamespaceCategoryFilter.prototype.onCategoryChange = function () {
		if ( !this.listenForChanges ) {
			return;
		}
		if ( this.categoryFilter.getValue() ) {
			this.listenForChanges = false;
			this.nsFilter.clear();
			this.listenForChanges = true;
		}
		this.onFilter();
	};

	bs.aggregatedStatistics.filter.NamespaceCategoryFilter.prototype.getValue = function () {
		return Object.assign( {}, this.categoryFilter.getValue(), this.nsFilter.getValue() );
	};

}( mediaWiki, jQuery, blueSpice ) );
