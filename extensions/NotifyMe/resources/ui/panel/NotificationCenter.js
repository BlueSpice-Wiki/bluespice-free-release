ext.notifyme.ui.panel.NotificationCenter = function ( cfg ) {
	cfg = cfg || {};

	cfg.expanded = false;

	this.filter = 'title-all';
	this.mobileView = cfg.mobileView || false;

	// Parent constructor
	ext.notifyme.ui.panel.NotificationCenter.parent.call( this, cfg );
	// Mixin constructors
	OO.ui.mixin.PendingElement.call( this, cfg );

	this.tabSelectWidget.$element.addClass( 'notifications-ui-panel-NotificationCenter-TabSelectWidget' );

	this.itemPerPage = cfg.itemPerPage || 10;

	this.addTabs();

	this.connect( this, {
		set: 'tabSelected'
	} );

	this.$element.addClass( 'notifications-ui-panel-NotificationCenter' );

	this.stickyPos = 0;

	this.toolbarWidth = 0;
	this.toolbarOffsetLeft = 0;

	$( window ).on( 'scroll', () => {
		const $toolbar = $( '.notifications-ui-panel-NotificationCenter .oo-ui-menuLayout-menu' );

		if ( this.stickyPos === 0 ) {
			this.stickyPos = $toolbar[ 0 ].offsetTop;

			// Preserve toolbar width to be the same even in sticky position
			// Same with "left offset"
			this.toolbarWidth = $toolbar.width();
			this.toolbarOffsetLeft = $toolbar[ 0 ].offsetLeft;
		}

		if ( window.pageYOffset >= this.stickyPos ) {
			$toolbar.addClass( 'sticky' );

			const style = 'width: ' + this.toolbarWidth + 'px !important;' + 'left: ' + this.toolbarOffsetLeft + 'px'; // eslint-disable-line no-useless-concat
			$toolbar.attr( 'style', style );
		} else {
			$toolbar.removeClass( 'sticky' );

			$toolbar.attr( 'style', '' );
		}
	} );

	// If there are no unread notifications - no need to show "Unread" tab
	const unreadNotifications = $( '#notifications-mm input[name=unreadNotificationsCount]' ).val();
	if ( unreadNotifications === '0' ) {
		this.setTabPanel( 'read' );
	}

	this.getCurrentTabPanel().loadNotifications();

	// Prepend filter widget
	this.addFilterWidget();
};

OO.inheritClass( ext.notifyme.ui.panel.NotificationCenter, OO.ui.IndexLayout );
OO.mixinClass( ext.notifyme.ui.panel.NotificationCenter, OO.ui.mixin.PendingElement );

ext.notifyme.ui.panel.NotificationCenter.prototype.addTabs = function () {
	this.tabPanelUnread = new ext.notifyme.ui.panel.NotificationTab(
		'unread', {
			label: mw.message( 'notifyme-notification-center-tab-label-unread' ).text(),
			expanded: false,
			itemPerPage: this.itemPerPage
		}
	);

	this.tabPanelRead = new ext.notifyme.ui.panel.NotificationTab(
		'read', {
			label: mw.message( 'notifyme-notification-center-tab-label-read' ).text(),
			expanded: false,
			itemPerPage: this.itemPerPage
		}
	);

	this.tabPanelAll = new ext.notifyme.ui.panel.NotificationTab(
		'all', {
			label: mw.message( 'notifyme-notification-center-tab-label-all' ).text(),
			expanded: false,
			itemPerPage: this.itemPerPage
		}
	);

	this.tabPanelUnread.connect( this, {
		filterDataRetrieved: 'updateFilter',
		markAllRead: 'markNotificationsAllRead'
	} );
	this.tabPanelRead.connect( this, {
		filterDataRetrieved: 'updateFilter'
	} );
	this.tabPanelAll.connect( this, {
		filterDataRetrieved: 'updateFilter',
		markAllRead: 'markNotificationsAllRead'
	} );

	this.addTabPanels( [ this.tabPanelUnread, this.tabPanelRead, this.tabPanelAll ] );
};

ext.notifyme.ui.panel.NotificationCenter.prototype.addFilterWidget = function () {
	this.filterWidget = new ext.notifyme.ui.widget.FilterWidget( {
		mobileView: this.mobileView
	} );

	this.filterWidget.connect( this, {
		selectItem: 'filterItemSelected'
	} );

	$( '#notifications-overview' ).prepend( this.filterWidget.$element );
};

/**
 * Fires when one of filters was selected.
 * If new filter was selected - re-loads notifications.
 *
 * @param {string} key Filter key, for example "namespaces-0", "all", and so on
 */
ext.notifyme.ui.panel.NotificationCenter.prototype.filterItemSelected = function ( key ) {
	this.filter = key;

	const tab = this.getCurrentTabPanel();

	// If filter changed - re-load notifications after applying filter
	if ( key !== tab.getFilter() ) {
		tab.applyFilter( this.filter );
		tab.loadNotifications();
	}
};

/**
 * Fires when specific tab is selected.
 * Applies global filter to this specific tab and re-loads notifications.
 *
 * @param {ext.notifyme.ui.panel.NotificationTab} tab Object of selected tab
 */
ext.notifyme.ui.panel.NotificationCenter.prototype.tabSelected = function ( tab ) {
	tab.applyFilter( this.filter );
	tab.loadNotifications();

	this.tabSelectWidget.$element.trigger( 'focus' );
};

/**
 * Fires when notifications are loaded (and filter data with them).
 * Filter data is gathered depending on notifications (like how many notifications from specific namespace etc.)
 *
 * @param {Object} filterData
 * @param {string} activeFilter
 */
ext.notifyme.ui.panel.NotificationCenter.prototype.updateFilter = function ( filterData, activeFilter ) {
	this.filterWidget.loadData( filterData, activeFilter );
};

/**
 * Mark all unread user notifications as "read"
 */
ext.notifyme.ui.panel.NotificationCenter.prototype.markNotificationsAllRead = function () {
	ext.notifyme.setReadStatus( { '*': true } ).done( ( response ) => {
		const notifications = response;
		for ( const id in notifications ) {
			const isProcessed = notifications[ id ];
			if ( !isProcessed ) {
				console.error( 'Failed to change read status of notification: ' + id ); // eslint-disable-line no-console
			}
		}

		this.getCurrentTabPanel().loadNotifications();
	} );
};
