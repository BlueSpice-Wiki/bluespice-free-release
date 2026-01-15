( ( mw, $ ) => {

	$( async () => {
		const deps = require( './config.json' ).pageAssignmentsOverviewDeps;
		await mw.loader.using( deps );

		const $container = $( '#bs-pageassignments-overview' ); // eslint-disable-line no-jquery/no-global-selector
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.pageassignments.ui.panel.Overview();

		$container.append( panel.$element );
	} );

} )( mediaWiki, jQuery );
