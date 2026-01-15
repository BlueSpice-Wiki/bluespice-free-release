( function ( mw, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics' );

	bs.aggregatedStatistics.ReportRegistry = function ( reports ) {
		bs.aggregatedStatistics.ReportRegistry.parent.call( this );
		OO.EventEmitter.call( this );

		let modules = [];
		const classes = {};
		for ( const key in reports ) {
			if ( !reports.hasOwnProperty( key ) ) {
				continue;
			}
			modules = modules.concat( reports[ key ].rlModules );
			classes[ key ] = reports[ key ].class;
		}

		mw.loader.using( modules, () => {
			for ( const key in classes ) {
				if ( !classes.hasOwnProperty( key ) ) {
					continue;
				}
				this.register( key, this.callbackFromString( classes[ key ] ) );
			}
			this.emit( 'registrationComplete' );
		} );
	};

	OO.inheritClass( bs.aggregatedStatistics.ReportRegistry, OO.Registry );
	OO.mixinClass( bs.aggregatedStatistics.ReportRegistry, OO.EventEmitter );

	bs.aggregatedStatistics.ReportRegistry.prototype.callbackFromString = function ( callback ) {
		const parts = callback.split( '.' );
		let func = window[ parts[ 0 ] ];
		for ( let i = 1; i < parts.length; i++ ) {
			func = func[ parts[ i ] ];
		}

		return func;
	};

}( mediaWiki, blueSpice ) );
