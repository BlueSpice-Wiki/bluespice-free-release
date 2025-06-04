ext.notifyme.ui.widget.NotificationGroupWidget = function ( data, cfg ) {
	cfg = cfg || {};

	this.message = data.message;
	this.count = data.count;
	this.notifications = data.notifications;

	this.expanded = false;

	let agent;
	if ( this.notifications[ 0 ].agent_is_bot ) {
		agent = null;
	} else {
		agent = this.notifications[ 0 ].agent;
	}

	const notificationData = {
		id: this.notifications[ 0 ].id,
		// TODO: #icon
		// icon: this.notifications[0].icon,
		icon: 'article',
		agent: agent,
		message: this.notifications[ 0 ].message,
		unread: this.isGroupUnread(),
		timestamp: this.notifications[ 0 ].timestamp,
		links: this.notifications[ 0 ].links,
		isInGroup: false
	};

	ext.notifyme.ui.widget.NotificationGroupWidget.parent.call( this, notificationData, cfg );
	this.connect( this, {
		itemMarked: 'groupItemMarked'
	} );

	this.addNotifications( this.notifications );

	this.$element.addClass( 'notifications-ui-widget-NotificationGroupWidget' );
};

OO.inheritClass( ext.notifyme.ui.widget.NotificationGroupWidget, ext.notifyme.ui.widget.NotificationItemWidget );

ext.notifyme.ui.widget.NotificationGroupWidget.prototype.isGroupUnread = function () {
	return this.notifications[ 0 ].status === 'pending';
};

ext.notifyme.ui.widget.NotificationGroupWidget.prototype.alterBody = function ( $body ) {
	let expandedLabelMessage;
	if ( this.expanded ) {
		expandedLabelMessage = mw.message( 'notifyme-notifications-group-expander-label-expanded' );
	} else {
		expandedLabelMessage = mw.message( 'notifyme-notifications-group-expander-label-collapsed' );
	}

	this.expander = new OO.ui.ButtonWidget( {
		icon: this.expanded ? 'collapse' : 'expand',
		label: expandedLabelMessage.params( [ this.count - 1 ] ).text(),
		framed: false,
		classes: [ 'notifications-ui-widget-NotificationGroupWidget-expand-wrapper' ]
	} );

	this.expander.connect( this, {
		click: 'toggle'
	} );

	$body.append( this.expander.$element );

	return $body;
};

ext.notifyme.ui.widget.NotificationGroupWidget.prototype.toggle = function ( target ) { // eslint-disable-line no-unused-vars
	this.expanded = !this.expanded;

	let expandedLabelMessage;
	if ( this.expanded ) {
		expandedLabelMessage = mw.message( 'notifyme-notifications-group-expander-label-expanded' );
	} else {
		expandedLabelMessage = mw.message( 'notifyme-notifications-group-expander-label-collapsed' );
	}

	// Update icon and label
	this.expander.setLabel( expandedLabelMessage.params( [ this.count - 1 ] ).text() );
	this.expander.setIcon( this.expanded ? 'collapse' : 'expand' );

	// Expand notifications
	this.$expandedNotificationsWrapper.toggleClass( 'notifications-ui-widget-NotificationGroupWidget-notifications-expanded' );
};

ext.notifyme.ui.widget.NotificationGroupWidget.prototype.addNotifications = function ( notifications ) {
	this.$expandedNotificationsWrapper = $( '<div>' ).addClass( 'notifications-ui-widget-NotificationGroupWidget-notifications' );

	for ( let i = 1; i < notifications.length; i++ ) {
		let agent;
		if ( this.notifications[ i ].agent_is_bot ) {
			agent = null;
		} else {
			agent = this.notifications[ i ].agent;
		}

		const notificationData = {
			id: notifications[ i ].id,
			icon: 'article',
			// TODO: #icon
			// icon: notifications[i].icon,
			agent: agent,
			message: notifications[ i ].message,
			unread: notifications[ i ].status === 'pending',
			timestamp: notifications[ i ].timestamp,
			links: notifications[ i ].links,
			isInGroup: true
		};

		const notification = new ext.notifyme.ui.widget.NotificationItemWidget( notificationData );
		notification.connect( this, {
			itemMarked: 'groupItemMarked'
		} );

		this.$expandedNotificationsWrapper.append( notification.$element );
	}

	this.$element.append( this.$expandedNotificationsWrapper );
};

ext.notifyme.ui.widget.NotificationGroupWidget.prototype.groupItemMarked = function () {
	this.emit( 'groupItemMarked' );
};
