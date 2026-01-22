ext.notifyme.ui.panel.NotificationTab = function ( name, cfg ) {
	cfg = cfg || {};

	this.paginationResetNeeded = false;

	this.page = 1;
	this.total = 0;

	// This is used to keep track of how many notifications are actually shown per page
	// Can be different than "page size", as multiple notifications can be grouped in one group
	this.shownPerPage = {};

	this.itemPerPage = cfg.itemPerPage || 10;

	this.filter = 'title-all';
	this.notificationStatus = 'all';

	// Parent constructor
	ext.notifyme.ui.panel.NotificationTab.parent.call( this, name, cfg );

	// Pagination
	this.paginationWidget = new ext.notifyme.ui.widget.PaginationWidget( {
		itemPerPage: this.itemPerPage
	} );
	this.paginationWidget.connect( this, {
		change: 'changePage'
	} );

	this.markAllReadBtn = new OO.ui.ButtonWidget( {
		icon: 'checkAll',
		label: mw.message( 'notifyme-notification-center-help-menu-mark-all-read-label' ).text(),
		data: 'markAllRead',
		framed: false,
		flags: [ 'progressive' ],
		classes: [ 'notifications-ui-panel-NotificationTab-markAllAllRead-btn' ]
	} );

	// Notifications wrapper
	this.$notificationsWrapper = $( '<div>' ).addClass( 'notifications-ui-panel-NotificationTab-notifications' );

	this.$element.append( this.paginationWidget.$element );

	if ( this.name !== 'read' ) {
		this.$element.append( this.markAllReadBtn.$element );
		this.markAllReadBtn.connect( this, {
			click: () => {
				this.emit( 'markAllRead' );
			}
		} );
	}

	this.$element.append( this.$notificationsWrapper );
};

OO.inheritClass( ext.notifyme.ui.panel.NotificationTab, OO.ui.TabPanelLayout );

/**
 * Returns current active filter
 *
 * @return {string} Filter key
 */
ext.notifyme.ui.panel.NotificationTab.prototype.getFilter = function () {
	return this.filter;
};

/**
 * Applies specified filter, re-loads notifications and resets pagination if new filter differs from old one
 *
 * @param {string} key Filter key, for example "namespaces-0", "all", and so on
 */
ext.notifyme.ui.panel.NotificationTab.prototype.applyFilter = function ( key ) {
	if ( key === this.filter ) {
		return;
	}

	this.filter = key;
	this.paginationResetNeeded = true;
};

/**
 * Changes page and re-loads notifications
 *
 * @param {string} dir
 */
ext.notifyme.ui.panel.NotificationTab.prototype.changePage = function ( dir ) {
	if ( dir === 'next' ) {
		if ( !this.paginationWidget.hasNextPage() ) {
			return;
		}

		this.paginationWidget.nextPage();
		this.page++;
	} else if ( dir === 'prev' ) {
		if ( !this.paginationWidget.hasPrevPage() ) {
			return;
		}

		this.paginationWidget.prevPage();
		this.page--;
	}

	this.loadNotifications();
};

ext.notifyme.ui.panel.NotificationTab.prototype.loadNotifications = function () {

	function _showLoading() { // eslint-disable-line no-underscore-dangle
		if ( $( '.notifications-ui-panel-NotificationTab-loading' ).length > 0 ) {
			return;
		}

		const pbWidget = new OO.ui.ProgressBarWidget( {
			progress: false
		} );

		const $notificationsWrapper = $( '.notifications-ui-panel-NotificationTab-notifications' );

		// Insert loader before results div to avoid resetting it
		$notificationsWrapper.before(
			$( '<div>' )
				.addClass( 'notifications-ui-panel-NotificationTab-loading' )
				.append( pbWidget.$element )
		);
		$notificationsWrapper.hide();
		$( '.notifications-ui-widget-PaginationWidget-row' ).hide();
	}

	function _removeLoading() { // eslint-disable-line no-underscore-dangle
		$( '.notifications-ui-panel-NotificationTab-loading' ).remove();
		$( '.notifications-ui-panel-NotificationTab-notifications' ).show();
		$( '.notifications-ui-widget-PaginationWidget-row' ).show();
	}

	_showLoading();

	// Reset pagination if filter changed
	if ( this.paginationResetNeeded ) {
		this.page = 1;
		this.paginationWidget.reset();

		this.paginationResetNeeded = false;
	}

	// Clear the tab before filling it
	this.$notificationsWrapper.empty();

	const offset = this.shownPerPage.hasOwnProperty( this.page - 1 ) ? this.shownPerPage[ this.page - 1 ] : 0;

	const params = {
		limit: this.itemPerPage,
		start: offset,
		filter: this.getFilters(),
		sort: [ {
			property: 'timestamp',
			direction: 'desc'
		} ]
	};

	ext.notifyme.retrieve( params ).done( ( response ) => {
		ext.notifyme.getFilterMetadata( this.notificationStatus ).done( ( filtersData ) => {
			this.emit( 'filterDataRetrieved', filtersData, this.filter );
		} );

		// Make sure that pagination is visible
		this.paginationWidget.toggle( true );

		let datedNotificationWidget = null, prevTimestamp = null, isUnread;
		const results = response.results || [];
		for ( const i in results ) {
			const entity = results[ i ];

			const isSingleNotification = entity.entity_type === 'single_notification';

			let entityWidget;

			if ( isSingleNotification ) {
				isUnread = entity.status === 'pending';

				let agent;
				if ( entity.agent_is_bot ) {
					agent = null;
				} else {
					agent = entity.agent;
				}

				entityWidget = new ext.notifyme.ui.widget.NotificationItemWidget( {
					id: entity.id,
					icon: entity.icon,
					agent: agent,
					message: entity.message,
					unread: isUnread,
					timestamp: entity.timestamp,
					links: entity.links,
					isInGroup: false
				} );
				entityWidget.connect( this, {
					itemMarked: 'itemsMarked'
				} );
			} else {
				entityWidget = new ext.notifyme.ui.widget.NotificationGroupWidget( {
					message: entity.message,
					count: entity.count,
					notifications: entity.notifications
				} );
				entityWidget.connect( this, {
					groupItemMarked: 'itemsMarked'
				} );

				isUnread = entityWidget.isGroupUnread();
			}

			let currentTimestamp;
			if ( isSingleNotification ) {
				currentTimestamp = entity.timestamp;
			} else {
				currentTimestamp = entity.notifications[ 0 ].timestamp;
			}

			// First entity (either notification or group of notifications) is added by default
			if ( datedNotificationWidget === null ) {
				datedNotificationWidget = new ext.notifyme.ui.widget.DatedNotificationsWidget( {
					timestamp: currentTimestamp,
					isUnread: isUnread,
					// cannot change status in an "all" tab, as group might have mixed statuses
					disableMassStatusChange: this.getName() === 'all'
				} );
				datedNotificationWidget.connect( this, {
					groupMarked: 'itemsMarked'
				} );

				datedNotificationWidget.addItem( entityWidget );
			}

			// If notifications were sent in one day - they are united in one dated group
			if ( prevTimestamp !== null ) {
				const prevDate = new Date( prevTimestamp );
				const currentDate = new Date( currentTimestamp );

				if (
					prevDate.getDate() === currentDate.getDate() &&
					prevDate.getMonth() === currentDate.getMonth() &&
					prevDate.getFullYear() === currentDate.getFullYear()
				) {
					datedNotificationWidget.addItem( entityWidget );
				} else {
					this.$notificationsWrapper.append( datedNotificationWidget.$element );

					// Another day - create new dated notifications group
					datedNotificationWidget = new ext.notifyme.ui.widget.DatedNotificationsWidget( {
						timestamp: currentTimestamp,
						isUnread: isUnread,
						// cannot change status in an "all" tab, as group might have mixed statuses
						disableMassStatusChange: this.getName() === 'all'
					} );
					datedNotificationWidget.connect( this, {
						groupMarked: 'itemsMarked'
					} );

					datedNotificationWidget.addItem( entityWidget );
				}
			}

			prevTimestamp = currentTimestamp;
		}

		this.total = response.total;
		this.shownPerPage[ this.page ] = this.page === 1 ?
			response.processed : this.shownPerPage[ this.page - 1 ] + response.processed;
		this.paginationWidget.updateWidget( response.items_total );

		_removeLoading();

		// If there are no notifications, show corresponding label and hide pagination
		if ( !results.length ) {
			const labelWidget = new OOJSPlus.ui.widget.NoContentPlaceholderWidget( {
				icon: 'no-notifications',
				label: mw.message( 'notifyme-notification-center-no-new-notifications-label' ).text()
			} );

			labelWidget.$element.addClass( 'notifications-ui-panel-NotificationTab-no-notifications-label' );

			this.$notificationsWrapper.append( labelWidget.$element );

			// Hide pagination
			this.paginationWidget.toggle( false );
			// Hide mark all
			this.markAllReadBtn.toggle( false );
		} else {
			this.$notificationsWrapper.append( datedNotificationWidget.$element );
		}
	} );
};

ext.notifyme.ui.panel.NotificationTab.prototype.getFilters = function () {
	const currentTabName = this.getName(),
		filters = [],
		unreadParam = currentTabName !== 'read' && currentTabName !== 'all',
		readParam = currentTabName === 'read';
	if ( this.filter !== 'title-all' ) {
		// If there is a filter with subitems ("Namespaces" filter, for instance)
		// Then filter item won't have key (because it is un-clickable)
		// But subitems will have "$filterKey-$subItemKey" keys
		const dashIndex = this.filter.indexOf( '-' );
		if ( dashIndex !== -1 ) {
			const filterKey = this.filter.slice( 0, Math.max( 0, dashIndex ) );
			const subItemKey = this.filter.slice( Math.max( 0, dashIndex + 1 ) );

			const filterObj = {
				property: filterKey,
				value: subItemKey,
				operator: 'eq',
				type: 'string'
			};

			if ( filterKey === 'category' && subItemKey !== '' ) {
				filterObj.operator = 'like';

				// Wire up with backend data provider
				filterObj.property = 'categories';
			}

			if ( filterKey === 'namespace' ) {
				filterObj.property = 'namespace_id';
			}

			filters.push( filterObj );
		} else {
			// We actually don't have any "single value" filters except "Everything"
			// So not sure what to do in that case...
		}
	}

	if ( unreadParam && !readParam ) {
		this.notificationStatus = 'pending';

		filters.push( {
			property: 'status',
			value: this.notificationStatus,
			operator: 'eq',
			type: 'string'
		} );
	}
	if ( readParam && !unreadParam ) {
		this.notificationStatus = 'completed';

		filters.push( {
			property: 'status',
			value: this.notificationStatus,
			operator: 'eq',
			type: 'string'
		} );
	}

	return filters;
};

ext.notifyme.ui.panel.NotificationTab.prototype.itemsMarked = function () {
	this.loadNotifications();
};
