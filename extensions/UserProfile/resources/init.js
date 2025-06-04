$( function() {
	function renderProfile( $cnt, cfg ) {
		var params = $cnt.data( 'params' ) || {};
		cfg = $.extend( {
			editable: params.editable || false,
			user: params.user,
			userDisplay: params.user_display || params.user,
			isOwn: params.own || false,
			framed: true
		}, cfg );

		var panel = new ext.userProfile.ui.ProfilePanel( cfg );
		panel.initialize();

		$cnt.prepend( panel.$element );
	}
	$( '.user-profile-on-user-page' ).each( function() {
		// We may need this distinction in the future
		var $cnt = $( this );
		renderProfile( $cnt, {
			isOnUserPage: true
		} );
	} );
	// Select all .user-profile but not .rendered
	$( '.user-profile:not(.rendered)' ).each( function() {
		var $cnt = $( this );
		renderProfile( $cnt, {
			framed: $cnt.data( 'framed' )
		} );
	} );
	$( '#userprofile-editor' ).each( function() {
		var $cnt = $( this );
		var editor = new ext.userProfile.ui.Editor( {
			user: $cnt.data( 'user' ),
			fields: $cnt.data( 'fields' ),
			data: $cnt.data( 'data' )
		} );
		editor.initialize();
		$cnt.append( editor.$element );
	} );
} );