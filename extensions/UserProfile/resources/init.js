$( () => {
	function renderProfile( $cnt, cfg ) {
		const params = $cnt.data( 'params' ) || {};
		cfg = Object.assign( {
			editable: params.editable || false,
			user: params.user,
			userDisplay: params.user_display || params.user,
			isOwn: params.own || false,
			framed: true
		}, cfg );

		const panel = new ext.userProfile.ui.ProfilePanel( cfg );
		panel.initialize();

		$cnt.prepend( panel.$element );
	}
	$( '.user-profile-on-user-page' ).each( function () {
		// We may need this distinction in the future
		const $cnt = $( this );
		renderProfile( $cnt, {
			isOnUserPage: true
		} );
	} );
	// Select all .user-profile but not .rendered
	$( '.user-profile:not(.rendered)' ).each( function () {
		const $cnt = $( this );
		renderProfile( $cnt, {
			framed: $cnt.data( 'framed' )
		} );
	} );
	$( '#userprofile-editor' ).each( function () {
		const $cnt = $( this );
		const editor = new ext.userProfile.ui.Editor( {
			user: $cnt.data( 'user' ),
			fields: $cnt.data( 'fields' ),
			data: $cnt.data( 'data' )
		} );
		editor.initialize();
		$cnt.append( editor.$element );
	} );
} );
