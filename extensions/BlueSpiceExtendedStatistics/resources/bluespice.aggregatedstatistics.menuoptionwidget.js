( function ( mw, $, bs ) {

	bs.util.registerNamespace( 'bs.aggregatedStatistics' );

	bs.aggregatedStatistics.MenuOptionWidget = function ( cfg ) {
		bs.aggregatedStatistics.MenuOptionWidget.parent.call( this, cfg );

		if ( !cfg.desc ) {
			return;
		}

		const $desc = $( '<span>' )
			.css( {
				'font-size': '0.9em',
				color: '#54595d'
			} )
			.html( cfg.desc );

		this.$element.append( $desc );
	};

	OO.inheritClass( bs.aggregatedStatistics.MenuOptionWidget, OO.ui.MenuOptionWidget );

}( mediaWiki, jQuery, blueSpice ) );
