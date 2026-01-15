$( () => {
	const $notificationsWrapper = $( '#notifications-overview' );

	mw.loader.using( [ 'ext.notifyme.notification.center' ], () => {
		const notificationCenter = new ext.notifyme.ui.panel.NotificationCenter( {
			itemPerPage: 10
		} );

		$notificationsWrapper.append( notificationCenter.$element );
	} );
} );
