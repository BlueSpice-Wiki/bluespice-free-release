$( () => {
	bs.util.registerNamespace( 'bs.instanceStatus.registry' );
	const pluginModules = require( './pluginModules.json' );

	// Init registry
	bs.instanceStatus.registry.Panels = new OO.Registry();
	// Load plugin modules that will populate the registry
	mw.loader.using( pluginModules ).done( () => {
		const booklet = new OO.ui.BookletLayout( {
			outlined: true,
			expanded: true,
			classes: [ 'bs-instance-status-booklet' ]
		} );

		const pages = [];
		for ( const key in bs.instanceStatus.registry.Panels.registry ) {
			const cls = bs.instanceStatus.registry.Panels.lookup( key );
			if ( typeof cls !== 'function' ) {
				continue;
			}
			const page = new cls( key, { booklet: booklet } ); // eslint-disable-line new-cap
			if ( page instanceof OO.ui.PageLayout ) {
				pages.push( page );
			}
		}
		booklet.addPages( pages );
		if ( pages.length === 1 ) {
			booklet.toggleMenu( false );
		} else {
			booklet.selectFirstSelectablePage();
		}

		$( '#bs-instance-status-overview' ).html( booklet.$element );
	} ).fail( () => {
		// eslint-disable-next-line no-console
		console.error( 'Failed to load plugin modules for BlueSpiceInstanceStatus' );
	} );
} );
