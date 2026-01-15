$( () => {
	window.megaMenuOpened = false;

	function updateUnreadNotificationsIndicator( unreadNotifications ) { // eslint-disable-line no-shadow
		// If there are unread notifications - add corresponding CSS indicator
		// Also add number of unread notifications there - up to "99+"
		if ( unreadNotifications > 0 ) {
			$megaMenuButton.addClass( 'attention-indicator' ); // eslint-disable-line no-use-before-define

			if ( unreadNotifications < 100 ) {
				$megaMenuButton.attr( 'data-unread-count', unreadNotifications ); // eslint-disable-line no-use-before-define
			} else {
				$megaMenuButton.attr( 'data-unread-count', '99+' ); // eslint-disable-line no-use-before-define
			}

			const unreadNotificationsAriaText = mw.message(
				'notifyme-navbar-button-aria-label-unread-notifications',
				unreadNotifications
			).text();

			$megaMenuButton.attr( 'aria-label', unreadNotificationsAriaText ); // eslint-disable-line no-use-before-define
		} else {
			$megaMenuButton.removeClass( 'attention-indicator' ); // eslint-disable-line no-use-before-define

			// If there are no unread notifications - no need to announce that
			$megaMenuButton.attr( 'aria-label', '' ); // eslint-disable-line no-use-before-define

			$megaMenuButton.attr( 'data-unread-count', 0 ); // eslint-disable-line no-use-before-define
		}
	}

	function markNotificationsAsRead( notificationsToMark ) {
		if ( notificationsToMark.length === 0 ) {
			return;
		}

		// Convert array [ 'a', 'b'] to { 'a': true, 'b': true }
		const notifications = {};
		notificationsToMark.forEach( ( id ) => {
			notifications[ id ] = true;
		} );

		ext.notifyme.setReadStatus( notifications ).done( ( response ) => {
			const notifications = response; // eslint-disable-line no-shadow
			for ( const id in notifications ) {
				const isProcessed = notifications[ id ];
				if ( !isProcessed ) {
					console.error( 'Failed to change read status of notification: ' + id ); // eslint-disable-line no-console
				}
			}
		} );
	}

	function renderNotificationCenterLink() {
		const $notificationCenterLinkWrapper = $( '<div>' ).addClass( 'notification-center-link' );

		const linkText = mw.message( 'notifyme-notifications-mega-menu-all-notifications-label' ).text();
		const href = new mw.Title( 'NotificationCenter', -1 ).getUrl();

		const $notificationCenterLink = $( '<a>' ).text( linkText ).attr( 'href', href );

		$notificationCenterLinkWrapper.append( $notificationCenterLink );

		return $notificationCenterLinkWrapper;
	}

	function renderNoNotifications() {
		const $noNotificationsWrapper = $( '<div>' ).addClass( 'notifications-no-notifications-wrapper' );

		const noNotificationsText = mw.message( 'notifyme-notifications-mega-menu-no-notifications' ).text();

		const $noNotificationsLabel = $( '<p>' ).text( noNotificationsText );

		const $notificationCenterLink = renderNotificationCenterLink();

		$noNotificationsWrapper.append( [
			$( '<div>' ).addClass( 'notifications-no-notifications-image' ),
			$noNotificationsLabel,
			$notificationCenterLink
		] );

		return $noNotificationsWrapper;
	}

	function renderNotificationItem( notification ) {
		const $notificationItem = $( '<div>' ).addClass( 'notification-item' );

		const $unreadCircleWrapper = $( '<div>' ).addClass( 'notification-unread-circle-wrapper' );
		const $unreadCircle = $( '<div>' ).addClass( 'notification-unread-circle' );

		$unreadCircleWrapper.append( $unreadCircle );

		const hasAgent = !notification.agent_is_bot && notification.entity_type === 'single_notification';
		const agent = hasAgent ? notification.agent.display_name : '';
		const $message = $( '<div>' ).addClass( 'notification-message' );
		$message.html( agent ? agent + ' ' + notification.message.main : notification.message.main );

		// Timestamp
		const itemMoment = moment.utc( notification.timestamp );
		itemMoment.local();

		const $timestamp = $( '<div>' ).addClass( 'notification-timestamp' );
		$timestamp.text( itemMoment.fromNow( false ) );

		$notificationItem.append( [
			$unreadCircleWrapper,
			$message,
			$timestamp
		] );

		return $notificationItem;
	}

	var $megaMenuButton = $( '.notifications-megamenu-btn' ); // eslint-disable-line no-var
	const $megamenu = $( '#notifications-mm' );

	let unreadNotifications = $megamenu.find( 'input[name=unreadNotificationsCount]' ).val() || 0;

	updateUnreadNotificationsIndicator( unreadNotifications );

	// Implicitly mark as "read" all unread notifications
	// when user opens mega menu
	// Request is sent only the first time when notifications menu is opened
	$megaMenuButton.on( 'click', () => {
		// We do not need to do anything if user just closed menu
		if ( window.megaMenuOpened ) {
			window.megaMenuOpened = false;

			return;
		}

		window.megaMenuOpened = true;

		const $notificationsMenuCard = $megamenu.find( '#notifications-card' );

		// Remove notifications which could probably left after previous opening
		$notificationsMenuCard.find( '.notifications-list-wrapper' ).remove();
		$notificationsMenuCard.find( '.notifications-no-notifications-wrapper' ).remove();

		// Request and display notifications
		// We are doing that on demand with API in JS to not produce extra load when page is loading
		const params = {
			group: true,
			limit: 10,
			filter: [
				{
					property: 'status',
					value: 'pending',
					operator: 'eq',
					type: 'string'
				}
			],
			sort: [ {
				property: 'timestamp',
				direction: 'desc'
			} ]
		};

		ext.notifyme.retrieve( params ).done( ( response ) => {
			// All shown notifications will be implicitly marked as read after retrieving
			let notificationsToMark = [];

			const results = response.results || [];

			if ( results.length === 0 ) {
				$notificationsMenuCard.append( renderNoNotifications() );

				return;
			}

			const $notificationsListWrapper = $( '<div>' ).addClass( 'notifications-list-wrapper' );

			const $notificationsList = $( '<div>' ).addClass( 'notifications-list' );

			let colsCount = 1;
			if ( results.length > 5 ) {
				colsCount = 2;
			}

			for ( let i = 0; i < colsCount; i++ ) {
				const classes = [ 'notifications-col' ];
				if ( colsCount === 1 ) {
					classes.push( 'notifications-single-col' );
				}

				const $notificationsCol = $( '<div>' ).addClass( classes ); // eslint-disable-line mediawiki/class-doc

				for ( let j = 0; j < 5; j++ ) {
					if ( ( i * 5 + j ) > results.length - 1 ) {
						break;
					}

					const notification = results[ i * 5 + j ];

					// Save notification ID to mark it as read
					const ids = notification.entity_type === 'single_notification' ?
						[ notification.id ] :
						notification.notifications.map( ( n ) => n.id );
					notificationsToMark = notificationsToMark.concat( ids );

					$notificationsCol.append( renderNotificationItem( notification ) );
				}

				$notificationsList.append( $notificationsCol );
			}

			$notificationsListWrapper.append( [
				$notificationsList,
				renderNotificationCenterLink()
			] );

			$notificationsMenuCard.append( $notificationsListWrapper );
			markNotificationsAsRead( notificationsToMark );

			// Update "unread notifications" indicator
			unreadNotifications -= results.length;

			updateUnreadNotificationsIndicator( unreadNotifications );
		} );
	} );
} );
