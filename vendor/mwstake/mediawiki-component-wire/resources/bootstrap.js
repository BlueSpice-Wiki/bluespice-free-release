'use strict';

/* eslint-disable no-underscore-dangle */
mws = window.mws || {};
mws.wire = {
	_initializing: false,
	_connectionTimer: null,
	_reconnectTimer: null,
	_subscriptionPermissions: {},
	_subscriptions: {},
	_url: mw.config.get( 'mwsgWireServiceWebsocketUrl' ),
	_parseMessage: ( message ) => mw.msg( ...message ),
	_checkCurrentUserCanSubscribe: ( channel ) => mws.wire._request( '/mws/v1/wire/check-can-subscribe', {
		channel: channel
	} ),
	_request: ( path, data ) => {
		const dfd = $.Deferred();
		$.ajax( {
			url: mw.util.wikiScript( 'rest' ) + path,
			type: 'POST',
			dataType: 'json',
			contentType: 'application/json; charset=utf-8',
			data: JSON.stringify( data )
		} ).done( ( data ) => { // eslint-disable-line no-shadow
			if ( data && data.value ) {
				dfd.resolve();
			} else {
				dfd.reject();
			}
		} ).fail( ( jqXHR, textStatus, errorThrown ) => {
			dfd.reject( errorThrown );
		} );

		return dfd.promise();
	},
	_connect: async () => {
		if ( mws.wire._initialiting ) {
			return;
		}
		mws.wire._initialiting = true;
		if ( !mws.wire._url ) {
			return null;
		}
		if ( mws.wire.socket ) {
			return;
		}
		mw.loader.using( 'mediawiki.user' ).then( async () => {
			try {
				const token = await mws.tokenAuthenticator.generateToken( true );
				mws.wire.socket = new WebSocket( mws.wire._url + '?token=' + encodeURIComponent( token ) ); // eslint-disable-line n/no-unsupported-features/node-builtins
				mws.wire._initialiting = false;
				mws.wire._connectionTimer = setTimeout( () => {
					mws.wire.socket.close();
					mws.wire.socket = null;
					mws.wire._reconnect();
				}, 5000 );
				mws.wire.socket.onopen = () => {
					mws.wire._clearConnectionTimer();
				};
				mws.wire.socket.onmessage = ( event ) => {
					const wireMessage = mws.wire.Message.fromData( event.data );
					const channel = wireMessage.channel;
					const subscriptions = mws.wire._subscriptions[ channel ] || [];
					for ( const callback of subscriptions ) {
						try {
							callback( wireMessage.payload );
						} catch ( e ) {
							console.error( 'Error in wire message callback for channel:', channel, e );
						}
					}
				};
				mws.wire.socket.onclose = () => {
					mws.wire.socket = null;
					mws.wire._reconnect();
				};
			} catch ( e ) {
				console.error( 'Error during wire connection initialization', e );
			}
		} );
	},
	_reconnect: () => {
		mws.wire._clearConnectionTimer();
		mws.wire._reconnectTimer = setTimeout( () => {
			mws.wire._connect();
		}, 1000 );
	},
	_clearConnectionTimer: () => {
		if ( mws.wire._connectionTimer ) {
			clearTimeout( mws.wire._connectionTimer );
			mws.wire._connectionTimer = null;
		}
		if ( mws.wire._reconnectTimer ) {
			clearTimeout( mws.wire._reconnectTimer );
			mws.wire._reconnectTimer = null;
		}
	},
	_subscribe: function ( channel, callbacks ) {
		mws.wire._connect();
		if ( !mws.wire._subscriptions[ channel ] ) {
			mws.wire._subscriptions[ channel ] = [];
		}
		for ( let i = 0; i < callbacks.length; i++ ) {
			const callback = callbacks[ i ];
			if ( typeof callback !== 'function' ) {
				continue;
			}
			this._subscriptions[ channel ].push( callback );
		}
	},
	listen: async function ( channel, callback ) {
		if ( !channel || typeof callback !== 'function' ) {
			return;
		}
		const user = mw.user.getName() || 'anonymous';
		mws.wire._subscriptionPermissions[ user ] = mws.wire._subscriptionPermissions[ user ] || {};
		mws.wire._subscriptionPermissions[ user ][ channel ] = mws.wire._subscriptionPermissions[ user ][ channel ] || {
			allowed: null,
			pendingCallbacks: [],
			apiCall: null
		};
		if ( mws.wire._subscriptionPermissions[ user ][ channel ].allowed === false ) {
			return;
		}
		if ( mws.wire._subscriptionPermissions[ user ][ channel ].allowed === true ) {
			mws.wire._subscribe( channel, [ callback ] );
			return;
		}
		mws.wire._subscriptionPermissions[ user ][ channel ].pendingCallbacks.push( callback );
		if ( mws.wire._subscriptionPermissions[ user ][ channel ].apiCall === null ) {
			mws.wire._subscriptionPermissions[ user ][ channel ].apiCall = mws.wire._checkCurrentUserCanSubscribe( channel )
				.done( () => {
					mws.wire._subscriptionPermissions[ user ][ channel ].allowed = true;
					mws.wire._subscribe( channel, mws.wire._subscriptionPermissions[ user ][ channel ].pendingCallbacks );
				} )
				.fail( () => {
					mws.wire._subscriptionPermissions[ user ][ channel ].allowed = false;

				} )
				.always( () => {
					mws.wire._subscriptionPermissions[ user ][ channel ].pendingCallbacks = [];
					mws.wire._subscriptionPermissions[ user ][ channel ].apiCall = null;
				} );
		}

	},
	getGlobalChannel: function () {
		return 'global';
	},
	getCurrentPageChannel: function () {
		const namespaceId = mw.config.get( 'wgNamespaceNumber' );
		if ( namespaceId === -1 ) {
			return 'special-' + mw.config.get( 'wgCanonicalSpecialPageName' );
		}
		return 'page-' +
			mw.config.get( 'wgNamespaceNumber' ) +
			'|' +
			mw.config.get( 'wgTitle' ).replace( ' ', '_' );
	},
	getUserChannel: function ( userName ) {
		if ( !userName ) {
			userName = mw.config.get( 'wgUserName' ) || 'anonymous';
		}
		return 'user-' + userName;
	},
	send: function ( wireMessage ) {
		return mws.wire._request( '/mws/v1/wire/send', wireMessage.toJSON() );
	}
};
