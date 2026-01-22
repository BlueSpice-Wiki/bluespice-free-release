let userIsEditing = false;
let keepAliveTimer = null;

function keepAlive() {
	clearTimeout( keepAliveTimer );
	keepAliveTimer = setTimeout( () => {
		if ( !userIsEditing ) {
			return;
		}
		fetch( mw.util.wikiScript( 'rest' ) + '/bs/saferedit/v1/keep-alive', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify( {
				page: mw.config.get( 'wgPageName' )
			} )
		} );
		keepAlive();
	}, 5 * 60 * 1000 );
}

function emitStartEdit() {
	userIsEditing = true;
	fetch( mw.util.wikiScript( 'rest' ) + '/bs/saferedit/v1/start-edit', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify( {
			section: mw.config.get( 'bsSaferEditEditSection' ),
			page: mw.config.get( 'wgPageName' )
		} )
	} );
	keepAlive();
}

function emitStopEdit() {
	if ( !userIsEditing ) {
		return;
	}
	userIsEditing = false;
	if ( navigator.sendBeacon ) {
		navigator.sendBeacon(
			mw.util.wikiScript( 'rest' ) + '/bs/saferedit/v1/stop-edit',
			new Blob( [ JSON.stringify( {
				page: mw.config.get( 'wgPageName' )
			} ) ], { type: 'application/json' } )
		);
	}
}

if ( mw.config.get( 'bsSaferEditIsEditMode' ) ) {
	emitStartEdit();
}

mw.hook( 've.activationComplete' ).add( () => {
	emitStartEdit();
} );

mw.hook( 've.deactivationComplete' ).add( () => {
	emitStopEdit();
} );

window.addEventListener( 'beforeunload', () => {
	emitStopEdit();
} );

if ( mw.config.get( 'bsSaferEditDisplayWarning' ) ) {
	window.mws.wire.listen( window.mws.wire.getCurrentPageChannel(), ( payload ) => {
		if ( payload.action && payload.action === 'bsSaferEditWarning' ) {
			bs.alerts.remove( 'bs-saferedit-warning' );
			if ( payload.data.length === 0 ) {
				return;
			}
			if ( payload.data.excludeFor && payload.data.excludeFor === mw.user.getName() ) {
				// Exclude for current user
				return;
			}
			let msg = '';
			const type = payload.data.type || 'i18n';
			if ( type === 'explicit' ) {
				msg = payload.data.message;
			} else if ( type === 'with-users' ) {
				// Filter out mw.user.getName()
				const users = payload.data.users.filter( ( user ) => user !== mw.user.getName() );
				if ( users.length === 0 ) {
					// Should show user names, but none available
					return;
				}
				const displayNames = users
					.map( ( user ) => payload.data.userDisplayNames[ user ] || user );
				msg = mw.msg( // eslint-disable-line mediawiki/msg-doc
					payload.data.message,
					displayNames.join( ', ' ),
					displayNames.length
				);
			} else if ( payload.data.message ) {
				const params = payload.data.params || [];
				msg = mw.msg( payload.data.message, ...params ); // eslint-disable-line mediawiki/msg-doc
			}

			if ( msg ) {
				bs.alerts.add(
					'bs-saferedit-warning',
					msg,
					bs.alerts.TYPE_WARNING
				);
			}
		}
	} );
}
