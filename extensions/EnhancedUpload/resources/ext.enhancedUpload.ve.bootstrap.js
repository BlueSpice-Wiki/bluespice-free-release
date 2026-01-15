const pluginModules = require( './pluginModules.json' );
mw.loader.using( pluginModules ).done( () => {
	mw.loader.using( 'ext.enhancedUpload.ve.transferHandler' );
} );
