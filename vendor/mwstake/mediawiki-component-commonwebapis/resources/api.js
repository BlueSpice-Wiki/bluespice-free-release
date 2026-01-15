var cache = { // eslint-disable-line no-var
	data: {},
	set: function ( key, data ) {
		cache.data[ key ] = data;
	},
	get: function ( key, defaultValue ) {
		return cache.data[ key ] || defaultValue;
	},
	has: function ( key ) {
		return cache.data[ key ] !== undefined;
	},
	delete: function ( key ) {
		if ( cache.has( key ) ) {
			delete ( cache.data[ key ] );
		}
	},
	getCachedPromise: function ( key, callback ) {
		if ( cache.has( key ) ) {
			return cache.get( key );
		}
		const promise = callback();
		cache.set( key, promise );
		promise.done( () => {
			cache.delete( key );
		} );

		return promise;
	}
};

function querySingle( store, property, value, cacheKey, recache ) {
	const dfd = $.Deferred();
	if ( !value || typeof value !== 'string' || value.length < 2 ) {
		return dfd.resolve( {} ).promise();
	}

	if ( !recache && cache.has( cacheKey ) ) {
		dfd.resolve( cache.get( cacheKey ) );
		return dfd.promise();
	}
	mws.commonwebapis[ store ].query( '', {
		filter: JSON.stringify( [
			{
				type: 'string',
				value: value,
				operator: 'eq',
				property: property
			}
		] ),
		limit: 1
	} ).done( ( response ) => {
		if ( response.length > 0 ) {
			dfd.resolve( response[ 0 ] );
			return;
		}
		dfd.resolve( {} );
	} ).fail( ( err ) => {
		dfd.resolve( err );
	} );

	return dfd.promise();
}

function queryStore( store, params, cacheKey ) {
	const dfd = $.Deferred();
	const req = $.ajax( {
		method: 'GET',
		url: mw.util.wikiScript( 'rest' ) + '/mws/v1/' + store,
		data: params
	} ).done( ( response ) => {
		if ( response && response.results ) {
			for ( let i = 0; i < response.results.length; i++ ) {
				const result = response.results[ i ];
				if ( !cacheKey ) {
					continue;
				}
				// Replace named placeholders in curly braces with actual values
				const key = cacheKey.replace( /\{([^}]+)\}/g, ( match, p1 ) => result[ p1 ] );
				// if cache key contains a placeholder that is not in the result, skip
				if ( key.indexOf( '{' ) !== -1 ) {
					continue;
				}
				cache.set( key, result );
			}
			dfd.resolve( response.results );
			return;
		}
		dfd.resolve( [] );
	} ).fail( ( err ) => {
		dfd.resolve( err );
	} );
	return dfd.promise( { abort: function () {
		req.abort();
	} } );
}

mws = window.mws || {};
mws.commonwebapis = {
	user: {
		query: function ( query, params ) {
			if ( query ) {
				params = ( params || {} ).query = query;
			}
			return queryStore( 'user-query-store', params, 'user-data-{user_name}' );
		},
		getByUsername: function ( username, recache ) {
			return cache.getCachedPromise( 'promise-user-data-' + username, () => querySingle( 'user', 'user_name', username, 'user-data-' + username, recache ) );
		}
	},
	group: {
		query: function ( query, params ) {
			if ( query ) {
				params = ( params || {} ).query = query;
			}
			return queryStore( 'group-store', params, 'group-{group_name}' );
		},
		getByGroupName: function ( groupname, recache ) {
			return cache.getCachedPromise( 'promise-group-data-' + groupname, () => querySingle(
				'group', 'group_name', groupname, 'group-' + groupname, recache
			) );
		}
	},
	title: {
		query: function ( query, params ) {
			return cache.getCachedPromise( 'promise-title-query', () => queryStore( 'title-query-store', Object.assign( { query: query }, params || {} ) ) );
		},
		getByPrefixedText: function ( prefixedText, recache ) {
			return cache.getCachedPromise( 'promise-title-data-' + prefixedText, () => querySingle(
				'title', 'prefixed', prefixedText, 'title-' + prefixedText, recache
			) );
		}
	},
	file: {
		query: function ( query, params ) {
			return cache.getCachedPromise( 'promise-file-query', () => queryStore( 'file-query-store', Object.assign( { query: query }, params || {} ) ) );
		}
	},
	category: {
		query: function ( query, params ) {
			return cache.getCachedPromise( 'promise-category-query', () => queryStore( 'category-query-store', Object.assign( { query: query }, params || {} ) ) );
		}
	}
};
