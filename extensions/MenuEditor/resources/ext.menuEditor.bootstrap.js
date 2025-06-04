window.ext = window.ext || {};

ext.menueditor = {
	init: {
		getPanelForPage: function ( pagename, menuType, revision, mode, cfg ) {
			const dfd = $.Deferred();
			mw.loader.using( [ 'ext.menuEditor.loader', 'ext.menuEditor.panel' ], () => {
				const loadPromise = ext.menueditor.init.loadEntities(),
					parsePromise = ext.menueditor.api.parse( pagename, revision ).then(
						( response ) => response,
						( error ) => {
							console.error( error ); // eslint-disable-line no-console
						}
					);

				$.when( loadPromise, parsePromise ).then(
					( loadResult, nodes ) => {
						// TODO: Check results, that menuType is registered...
						dfd.resolve(

							new ext.menueditor.ui.panel.MenuPanel( Object.assign( {
								pagename: pagename, expanded: false, mode: mode,
								toolbar: loadResult[ menuType ].toolbar
							}, cfg ), nodes, menuType )
						);
					},
					() => {
						dfd.reject();
					}
				);
			}, () => {
				dfd.reject();
			} );

			return dfd.promise();
		}
	},
	ui: {
		data: {
			tree: {},
			node: {}
		},
		panel: {},
		dialog: {},
		widget: {},
		tools: {}
	},
	registry: {},
	api: {
		parse: function ( pagename, revision, flat ) {
			const dfd = $.Deferred();
			mw.loader.using( 'ext.menuEditor.api', () => {
				const api = new ext.menueditor.api.Api();
				api.get( 'parse/{0}'.format( ext.menueditor.util.escapePagenameForRest( pagename ) ), {
					revid: revision || 0,
					flat: typeof flat === 'undefined' ? false : !!flat
				} ).done( ( data ) => {
					dfd.resolve( data );
				} ).fail( ( error ) => {
					dfd.reject( error );
				} );
			} );
			return dfd.promise();
		},
		save: function ( pagename, nodes ) {
			const dfd = $.Deferred();
			mw.loader.using( 'ext.menuEditor.api', () => {
				const api = new ext.menueditor.api.Api();
				api.post( ext.menueditor.util.escapePagenameForRest( pagename ), nodes )
					.done( ( response ) => {
						dfd.resolve( response );
					} )
					.fail( ( error ) => {
						dfd.reject( error );
					} );
			} );

			return dfd.promise();
		}
	},
	util: {
		callbackFromString: function ( str ) {
			const parts = str.split( '.' );
			let func = window[ parts[ 0 ] ];
			for ( let i = 1; i < parts.length; i++ ) {
				func = func[ parts[ i ] ];
			}

			return func;
		},
		escapePagenameForRest: function ( pagename ) {
			return mw.util.rawurlencode( mw.util.rawurlencode( pagename ) );
		}
	}
};
