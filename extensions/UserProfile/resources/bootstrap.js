/* eslint-disable no-underscore-dangle */
window.ext.userProfile = {
	ui: {},
	form: {},
	tag: {},
	_hideAllUserInfoPopups: function () {
		for ( const username in ext.userProfile._storage.userInfoPopups ) {
			if ( ext.userProfile._storage.userInfoPopupsTimeouts.opening[ username ] ) {
				// prevent scheduled opening
				delete ext.userProfile._storage.userInfoPopupsTimeouts.opening[ username ];
			}
			if ( ext.userProfile._storage.userInfoPopupsTimeouts.closing[ username ] ) {
				// clear scheduled closing
				clearTimeout( ext.userProfile._storage.userInfoPopupsTimeouts.closing[ username ] );
			}
			if ( ext.userProfile._storage.userInfoPopups.hasOwnProperty( username ) ) {
				// Toggle popup to close
				ext.userProfile._storage.userInfoPopups[ username ].toggle( false );
			}
		}
	},
	openUserInfoPopup: function ( username, $element ) {
		mw.loader.using( 'ext.userProfile.userInfoPopup', () => {
			ext.userProfile._hideAllUserInfoPopups();

			const popup = new ext.userProfile.ui.UserInfoPopup( {
				username: username,
				userPanel: ext.userProfile._storage.userInfoPanels[ username ] || null
			}, $element );

			if ( ext.userProfile._storage.userInfoPopupsTimeouts.opening[ username ] ) {
				clearTimeout( ext.userProfile._storage.userInfoPopupsTimeouts.opening[ username ] );
			}
			ext.userProfile._storage.userInfoPopupsTimeouts.opening[ username ] = setTimeout( () => {
				// Schedule opening
				$( 'body' ).append( popup.$element );
				popup.toggle( true );
			}, 1000 );
			if ( ext.userProfile._storage.userInfoPopupsTimeouts.closing[ username ] ) {
				// Clear any pending closing
				clearTimeout( ext.userProfile._storage.userInfoPopupsTimeouts.closing[ username ] );
				delete ext.userProfile._storage.userInfoPopupsTimeouts.closing[ username ];
			}
			popup.connect( popup, {
				infoReady: function ( userPanel ) {
					ext.userProfile._storage.userInfoPanels[ username ] = userPanel;
				},
				toggle: function ( visible ) {
					if ( visible ) {
						ext.userProfile._storage.userInfoPopups[ username ] = popup;
					} else if ( ext.userProfile._storage.userInfoPopups.hasOwnProperty( username ) ) {
						ext.userProfile._storage.userInfoPopups[ username ].$element.remove();
						delete ext.userProfile._storage.userInfoPopups[ username ];
					}
				}
			} );
			// Start loading content, while timeout is going on
			popup.makeContent();
			$element.attr( 'title', '' );
		} );
	},
	closeUserInfoPopup: function ( username ) {
		if ( ext.userProfile._storage.userInfoPopupsTimeouts.opening[ username ] ) {
			// If user leaves before popup is opened, cancel opening
			clearTimeout( ext.userProfile._storage.userInfoPopupsTimeouts.opening[ username ] );
			delete ext.userProfile._storage.userInfoPopupsTimeouts.opening[ username ];
		}
		// Close popup 1 second after user leaves
		ext.userProfile._storage.userInfoPopupsTimeouts.closing[ username ] = setTimeout( () => {
			if ( ext.userProfile._storage.userInfoPopups[ username ] ) {
				ext.userProfile._storage.userInfoPopups[ username ].toggle( false );
			}
		}, 1000 );
	},
	_storage: {
		userInfoPanels: {},
		userInfoPopups: {},
		userInfoPopupsTimeouts: { opening: {}, closing: {} }
	}
};

$( () => {
	$( document ).on( 'mouseenter', '#wrapper [data-bs-username]', function ( event ) { // eslint-disable-line no-unused-vars
		const $this = $( this ),
			username = $this.data( 'bs-username' );

		if ( username ) {
			// Schedule opening
			ext.userProfile.openUserInfoPopup( username, $this );
		}
	} );
	$( document ).on( 'mouseleave', '#wrapper [data-bs-username]', function ( event ) { // eslint-disable-line no-unused-vars
		const $this = $( this ),
			username = $this.data( 'bs-username' );
		if ( !username ) {
			return;
		}
		ext.userProfile.closeUserInfoPopup( username );
	} );
	// When user enters the popup, postpone closing until its left
	$( document ).on( 'mouseenter', '.ext-userprofile-userinfopopup', function ( event ) { // eslint-disable-line no-unused-vars
		const username = $( this ).attr( 'data-username' );
		if ( username && ext.userProfile._storage.userInfoPopupsTimeouts.closing[ username ] ) {
			clearTimeout( ext.userProfile._storage.userInfoPopupsTimeouts.closing[ username ] );
			delete ext.userProfile._storage.userInfoPopupsTimeouts.closing[ username ];
		}
	} );
	// When user leaves the popup, close it
	$( document ).on( 'mouseleave', '.ext-userprofile-userinfopopup', function ( event ) { // eslint-disable-line no-unused-vars
		const username = $( this ).attr( 'data-username' );
		if ( username ) {
			ext.userProfile.closeUserInfoPopup( username );
		}
	} );
	// On body click, close all popups unless click is on the popup
	$( document ).on( 'click', ( event ) => {
		if ( !$( event.target ).closest( '.ext-userprofile-userinfopopupext-userprofile-userinfopopup' ).length ) {
			// If user clicks away from the popup, close all popups
			ext.userProfile._hideAllUserInfoPopups();
		}
	} );
} );
