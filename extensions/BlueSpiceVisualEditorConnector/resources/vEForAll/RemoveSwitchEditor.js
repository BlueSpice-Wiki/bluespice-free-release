( () => {
	mw.loader.using( 'ext.veforall.main' ).then( () => {
		if ( typeof ve !== 'undefined' && ve.ui ) {
			const factory = ve.ui.toolFactory;
			const TOOL_NAME = 've4aSwitchEditor';

			if ( factory.lookup( TOOL_NAME ) ) {
				factory.unregister( TOOL_NAME );
			}
		}
	} );
} )();
