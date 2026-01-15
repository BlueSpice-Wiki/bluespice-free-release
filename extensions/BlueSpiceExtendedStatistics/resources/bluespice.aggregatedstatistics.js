$( function () {
	const $mainContainer = $( '#bs-extendedstatistics-special-aggregatedstatistics' );
	const pluginModules = require( './pluginModules.json' );

	const reportHandlers = $mainContainer.attr( 'data-reports' ),
		registry = new bs.aggregatedStatistics.ReportRegistry( JSON.parse( reportHandlers ) ),
		defaultFilters = JSON.parse( $mainContainer.attr( 'data-default-filter' ) );

	registry.connect( this, {
		registrationComplete: function () {
			const options = [];
			for ( const key in registry.registry ) {
				if ( !registry.registry.hasOwnProperty( key ) ) {
					continue;
				}
				options.push( new OO.ui.MenuOptionWidget( { data: key, label: registry.registry[ key ].static.label } ) );
			}
			dropdown.getMenu().addItems( options ); // eslint-disable-line no-use-before-define
			dropdown.setDisabled( false ); // eslint-disable-line no-use-before-define

			dropdown.getMenu().selectItemByData( Object.keys( registry.registry )[ 0 ] ); // eslint-disable-line no-use-before-define
		}
	} );

	var dropdown = new OO.ui.DropdownWidget( { // eslint-disable-line no-var
			id: 'statistic-selector',
			disabled: true
		} ),
		panel = new OO.ui.PanelLayout( { expanded: false } );

	const mainLayout = new OO.ui.FieldsetLayout( {
		label: mw.message( 'bs-statistics-aggregated-report-picker-label' ).text(),
		align: 'top',
		items: [
			dropdown,
			panel
		]
	} );

	$mainContainer.append( mainLayout.$element );

	dropdown.getMenu().connect( this, {
		select: function ( selected ) {
			if ( selected instanceof OO.ui.MenuOptionWidget ) {
				const cls = registry.lookup( selected.getData() );
				const item = new cls( { // eslint-disable-line new-cap
					defaultFilterValues: defaultFilters,
					name: selected.getData()
				} );

				panel.$element.html( item.$element );
				item.connect( this, {
					dataset: function ( data ) {
						mw.loader.using( pluginModules ).done( () => {
							mw.hook( 'aggregatedstatistics.addUI' ).fire( data );
						} );
					}
				} );
			}
		}
	} );

} );
