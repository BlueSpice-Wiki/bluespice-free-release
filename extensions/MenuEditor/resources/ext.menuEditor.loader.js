( function ( mw, $ ) {
	ext.menueditor.init.loadEntities = function () {
		const dfd = $.Deferred();

		function register( value, registry ) {
			const registerDfd = $.Deferred(), modules = [];
			for ( const name in value ) {
				if ( !value.hasOwnProperty( name ) ) { // eslint-disable-line no-prototype-builtins
					continue;
				}
				modules.push( value[ name ].module );
				registry.register( name, value[ name ].classname );
			}

			mw.loader.using( $.uniqueSort( modules ), () => {
				registerDfd.resolve();
			}, () => {
				registerDfd.reject( 'Cannot load modules:' + $.uniqueSort( modules ) );
			} );

			return registerDfd.promise();
		}

		ext.menueditor.registry.menu = new OO.Registry();
		ext.menueditor.registry.node = new OO.Registry();

		$.when(
			register( require( './menus.json' ), ext.menueditor.registry.menu, dfd ),
			register( require( './nodes.json' ), ext.menueditor.registry.node, dfd )
		).then( () => {
			dfd.resolve( require( './menus.json' ) );
		}, ( e ) => {
			console.error( e ); // eslint-disable-line no-console
			dfd.reject( e );
		} );
		return dfd.promise();
	};
}( mediaWiki, jQuery ) );
