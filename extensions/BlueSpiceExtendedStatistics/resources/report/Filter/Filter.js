( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.Filter = function ( cfg ) {
		bs.aggregatedStatistics.filter.Filter.super.call( this, cfg );
		this.required = cfg.required || false;
		OO.EventEmitter.call( this );

		this.init();
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.Filter, OO.ui.Widget );
	OO.mixinClass( bs.aggregatedStatistics.filter.Filter, OO.EventEmitter );

	bs.aggregatedStatistics.filter.Filter.prototype.init = function () {
		// STUB
	};

	bs.aggregatedStatistics.filter.Filter.prototype.getLayout = function () {
		// STUB
	};

	bs.aggregatedStatistics.filter.Filter.prototype.getValue = function () {
		// STUB
	};

	bs.aggregatedStatistics.filter.Filter.prototype.setValue = function ( value ) { // eslint-disable-line no-unused-vars
		// STUB
	};

}( mediaWiki, jQuery, blueSpice ) );
