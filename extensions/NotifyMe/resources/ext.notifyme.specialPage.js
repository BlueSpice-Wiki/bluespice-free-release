$( () => {
	const $notificationsWrapper = $( '#notifications-overview' );
	const isMobile = $( window ).width() < 767;
	if ( isMobile ) {
		$notificationsWrapper.addClass( 'notifications-mobile' );
	}

	mw.loader.using( [ 'ext.notifyme.notification.center' ], () => {
		const notificationCenter = new ext.notifyme.ui.panel.NotificationCenter( {
			mobileView: isMobile,
			itemPerPage: 10
		} );

		$notificationsWrapper.append( notificationCenter.$element );
	} );
} );
