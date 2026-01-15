( function ( mw, $, bs ) {

	bs.util.registerNamespace( 'bs.aggregatedStatistics.report' );

	bs.aggregatedStatistics.report.ReportBase = function ( cfg ) {
		bs.aggregatedStatistics.report.ReportBase.parent.call( this, {} );

		this.name = cfg.name;
		this.api = new mw.Api();
		this.defaultFilterValues = cfg.defaultFilterValues || {};
		this.appendStandardFilters();

		this.filters = this.getFilters();
		this.value = {};
		if ( this.filters.length > 0 ) {
			const filterLayout = new OO.ui.HorizontalLayout();
			for ( let i = 0; i < this.filters.length; i++ ) {
				this.filters[ i ].connect( this, {
					change: 'updateChart'
				} );
				if ( this.filters[ i ] instanceof bs.aggregatedStatistics.filter.IntervalFilter ) {
					this.defaultFilterLayout.$element.append( this.filters[ i ].$element );
				} else {
					filterLayout.$element.append( this.filters[ i ].$element );
				}
			}
			this.$element.append( filterLayout.$element );
		}

		this.$chartCnt = $( '<div id="chartCtn">' ); // eslint-disable-line no-jquery/no-parse-html-literal
		this.$element.append( this.$chartCnt );
		this.chart = this.getChart();
		this.updateChart();
	};

	OO.inheritClass( bs.aggregatedStatistics.report.ReportBase, OO.ui.Widget );

	bs.aggregatedStatistics.report.ReportBase.static.label = '';

	bs.aggregatedStatistics.report.ReportBase.static.desc = '';

	bs.aggregatedStatistics.report.ReportBase.static.tagName = 'div';

	// PUBLIC
	bs.aggregatedStatistics.report.ReportBase.prototype.getName = function () {
		return this.name;
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.getFilters = function () {
		return [];
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.appendStandardFilters = function () {
		this.dateFilter = new bs.aggregatedStatistics.filter.DateFilter();
		this.dateFilter.setValue( this.defaultFilterValues.date || null );
		this.dateFilter.connect( this, { change: 'updateChart' } );

		this.defaultFilterLayout = new OO.ui.HorizontalLayout( {
			classes: [ 'default-filters-layout' ],
			items: [ this.dateFilter ]
		} );
		this.$element.append( this.defaultFilterLayout.$element );
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.getChart = function () {
		return '';
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.isAggregate = function () {
		return false;
	};

	/**
	 * Returns label for any or all axis keys provided
	 *
	 * @return {Array}
	 */
	bs.aggregatedStatistics.report.ReportBase.prototype.getAxisLabels = function () {
		return {};
	};

	/**
	 * Called before data is set to the chart
	 *
	 * @param {Array} data
	 * @return {Array}
	 */
	bs.aggregatedStatistics.report.ReportBase.prototype.finalizeData = function ( data ) {
		return data;
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.setChartData = function ( filters ) {
		this.setLoading( true );
		this.query( filters ).done( ( data ) => {
			data = this.finalizeData( data );
			if ( data.length === 0 ) {
				this.setLoading( false );
				this.$chartCnt.html( this.getNoDataMessage() );
				this.emit( 'dataset', [] );
				return;
			}
			this.chart.setAxisLabels( this.getAxisLabels() );
			this.chart.updateData( data );

			this.setLoading( false );
			this.$chartCnt.html( this.chart.$element._groups[ 0 ] ); // eslint-disable-line no-underscore-dangle
			this.emit( 'dataset', data );
		} )
			.fail( ( err ) => {
				this.setLoading( false );
				this.showError( typeof err === 'string' ? err : null );
				console.error( err ); // eslint-disable-line no-console
			} );
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.query = function ( filter ) {
		this.api.abort();
		const dfd = $.Deferred();

		this.api.get( {
			action: 'query',
			meta: 'statistics-reports',
			esrfilter: JSON.stringify( filter ),
			esrtype: this.getName(),
			esraggregate: this.isAggregate() ? 1 : 0
		} ).done( ( response ) => {
			dfd.resolve( response.query[ 'statistics-reports' ][ this.getName() ] );
		} ).fail( ( response ) => {
			if ( response.hasOwnProperty( 'error' ) ) {
				dfd.reject( response.error.info );
			}
			dfd.reject( response );
		} );

		return dfd.promise();
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.updateChart = function () {
		let value = {};
		for ( let i = 0; i < this.filters.length; i++ ) {
			value = $.extend( value, this.filters[ i ].getValue() ); // eslint-disable-line no-jquery/no-extend
		}
		this.setChartData( Object.assign( {}, this.dateFilter.getValue(), value ) );
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.getNoDataMessage = function () {
		return $( '<h3>' ).text( mw.message( 'bs-statistics-aggregated-report-no-date' ).text() );
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.setLoading = function ( loading ) {
		if ( loading ) {
			this.$loading = $( '<h3>' ).text( mw.message( 'bs-statistics-aggregated-report-loading' ).text() );
			this.$chartCnt.html( this.$loading );
		} else if ( this.$loading ) {
			this.$loading.remove();
		}
	};

	bs.aggregatedStatistics.report.ReportBase.prototype.showError = function ( err ) {
		if ( !err ) {
			err = mw.message( 'bs-statistics-aggregated-report-error' ).text();
		}
		this.$chartCnt.html(
			$( '<div>' )
				.addClass( 'as-error-cnt' )
				.text( err )
		);
	};

}( mediaWiki, jQuery, blueSpice ) );
