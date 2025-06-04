/* eslint-disable no-underscore-dangle */
window.ext = window.ext || {};
window.ext.notifyme = {
	retrieve: function ( params ) {
		params = params || {};
		for ( const type in params ) {
			if ( !params.hasOwnProperty( type ) ) {
				continue;
			}
			if ( typeof params[ type ] === 'object' ) {
				params[ type ] = JSON.stringify( params[ type ] );
			}
		}
		return ext.notifyme._api.get( 'web', params );
	},
	getFilterMetadata: function ( status ) {
		// The only filter we use for "filter meta data" is "notification status" filter
		// That is done to receive filters corresponding to the current tab opened (Read, Unread, All)
		return ext.notifyme._api.get( 'web', { meta: 1, status: status } );
	},
	/**
	 * @param {Object} data
	 * "set as read": { notification_id: true }
	 * "set as unread": { notification_id: false }
	 * combined: { notification_id: true, other_notification_id: false }
	 * @return {*}
	 */
	setReadStatus: function ( data ) {
		if ( typeof data !== 'object' ) {
			console.error( 'setReadStatus: data must be an object' ); // eslint-disable-line no-console
		}
		return ext.notifyme._api.post( 'web/status', JSON.stringify( {
			notifications: data
		} ) );
	},
	_api: {
		get: function ( path, params ) {
			return ext.notifyme._api._ajax( path, params );
		},
		post: function ( path, params ) {
			return ext.notifyme._api._ajax( path, params, 'POST' );
		},
		_requests: {},
		_ajax: function ( path, data, method ) {
			data = data || {};
			const dfd = $.Deferred();

			ext.notifyme._api._requests[ path ] = $.ajax( {
				method: method,
				url: mw.util.wikiScript( 'rest' ) + '/notifications/' + path,
				data: data,
				contentType: 'application/json',
				dataType: 'json',
				beforeSend: function () {
					if ( ext.notifyme._api._requests.hasOwnProperty( path ) ) {
						ext.notifyme._api._requests[ path ].abort();
					}
				}
			} ).done( ( response ) => {
				delete ( ext.notifyme._api._requests[ path ] );
				dfd.resolve( response );
			} ).fail( ( jgXHR, type, status ) => {
				delete ( this._requests[ path ] );
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
	ui: {
		panel: {},
		dialog: {},
		subscriptionset: {
			editor: {}
		},
		widget: {}
	},
	channelConfigurationRegistry: new OO.Registry(),
	subscriptionSetRegistry: new OO.Registry()
};
