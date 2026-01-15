/* eslint-disable no-underscore-dangle, no-console */
window.ext = window.ext || {};
ext.contentdroplets = {
	// TODO: Register all namespaces needed here (dm, ce...)
	object: {},
	dm: {},
	ce: {},
	ui: {},
	api: {
		getDroplets: function () {
			const dfd = $.Deferred();

			$.ajax( {
				method: 'GET',
				url: mw.util.wikiScript( 'rest' ) + '/contentdroplets/droplets',
				data: {},
				contentType: 'application/json',
				dataType: 'json'
			} ).done( ( response ) => {
				dfd.resolve( response );
			} ).fail( ( jgXHR, type, status ) => {
				if ( type === 'error' ) {
					dfd.reject( {
						error: jgXHR.responseJSON || jgXHR.responseText
					} );
				}
				dfd.reject( { type: type, status: status } );
			} );

			return dfd.promise();
		}
	},
	registry: new OO.Registry(),
	getDroplets: function ( noCache ) {
		return this._get( 'droplets', noCache );
	},
	getCategories: function ( noCache ) {
		return this._get( 'categories', noCache );
	},
	_get: function ( key, noCache ) {
		const dfd = $.Deferred();

		// eslint-disable-next-line no-prototype-builtins
		if ( ext.contentdroplets._cache.hasOwnProperty( key ) && !noCache ) {
			return dfd.resolve( ext.contentdroplets._cache[ key ] ).promise();
		}
		this._doLoad().done( ( data ) => {
			dfd.resolve( data[ key ] );
		} ).fail( ( error ) => {
			dfd.reject( error );
		} );
		return dfd.promise();
	},
	_doLoad: function () {
		const dfd = $.Deferred();

		ext.contentdroplets.api.getDroplets().done( ( data ) => {
			const instances = {};
			const droplets = data.droplets;
			let modules = [ 'ext.forms.standalone' ],
				key, config, dropletClass;

			ext.contentdroplets._cache.categories = data.categories;

			for ( key in droplets ) {
				// eslint-disable-next-line no-prototype-builtins
				if ( !droplets.hasOwnProperty( key ) ) {
					continue;
				}
				modules = modules.concat( droplets[ key ].rlModules || [] );
			}

			mw.loader.using( modules, () => {
				for ( key in droplets ) {
					// eslint-disable-next-line no-prototype-builtins
					if ( !droplets.hasOwnProperty( key ) ) {
						continue;
					}
					delete ( droplets[ key ].rlModules );
					config = droplets[ key ];
					config.key = key;
					dropletClass = ext.contentdroplets.registry.lookup( key );
					if ( !dropletClass ) {
						if ( config.content.indexOf( 'target' ) !== -1 ) {
							instances[ key ] =
								new ext.contentdroplets.object.TransclusionDroplet( config );
							continue;
						}
						instances[ key ] = new ext.contentdroplets.object.Droplet( config );
					} else {
						// eslint-disable-next-line new-cap
						instances[ key ] = new dropletClass( config );
					}
				}

				ext.contentdroplets._cache.droplets = instances;
				dfd.resolve( ext.contentdroplets._cache );
			}, ( err ) => {
				ext.contentdroplets._cache = {};
				console.error( 'ContentDroplets: Required RL modules failed to load', err );
			} );
		} ).fail( ( error ) => {
			ext.contentdroplets._cache = {};
			console.error( 'ContentDroplets: ' + error );
		} );

		return dfd.promise();
	},
	_cache: {}
};

mw.hook( 've.collabpad.DropletsActivation' ).add( () => {
	ve.init.mw.CollabTarget.static.toolbarGroups.push( {
		include: [ 'contentdroplet-toolbar' ]
	} );
} );
