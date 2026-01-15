ext.notifyme.ui.widget.DatedNotificationsWidget = function ( cfg ) {
	cfg = cfg || {};

	this.isUnread = cfg.isUnread;
	this.disableMassStatusChange = cfg.disableMassStatusChange || false;

	ext.notifyme.ui.widget.DatedNotificationsWidget.parent.call( this, cfg );
	OO.EventEmitter.call( this );
	const itemMoment = moment.utc( cfg.timestamp );

	const timestampWidget = new OO.ui.LabelWidget( {
		$element: $( '<h2>' ),
		label: itemMoment.format( 'D MMMM YYYY' )
	} );
	timestampWidget.$element.addClass( 'notifications-ui-widget-DatedNotificationsWidget-date-label' );
	this.$element.append( timestampWidget.$element );

	if ( !this.disableMassStatusChange ) {
		let markGroupLabel;
		if ( cfg.isUnread ) {
			markGroupLabel = mw.message( 'notifyme-notifications-group-mark-group-as-read-label' ).text();
		} else {
			markGroupLabel = mw.message( 'notifyme-notifications-group-mark-group-as-unread-label' ).text();
		}

		const markGroupWidget = new OO.ui.ButtonWidget( {
			label: markGroupLabel,
			framed: false
		} );
		markGroupWidget.$element.addClass( 'notifications-ui-widget-DatedNotificationsWidget-mark-as-read-button' );
		markGroupWidget.connect( this, {
			click: 'markGroup'
		} );

		this.$element.append( markGroupWidget.$element );
	}

	this.$element.addClass( 'notifications-ui-widget-DatedNotificationsWidget' );
};

OO.inheritClass( ext.notifyme.ui.widget.DatedNotificationsWidget, OO.ui.Widget );
OO.mixinClass( ext.notifyme.ui.widget.DatedNotificationsWidget, OO.EventEmitter );

ext.notifyme.ui.widget.DatedNotificationsWidget.prototype.addItem = function ( item ) {
	this.$element.append( item.$element );
};

ext.notifyme.ui.widget.DatedNotificationsWidget.prototype.markGroup = function () {
	const notificationsToMark = {};

	this.$element.find( '.notifications-ui-widget-NotificationItemWidget' ).each( function () {
		const $this = $( this );

		const isUnread = $this.attr( 'data-unread' ) === 'true';
		const id = $this.attr( 'data-id' );

		notificationsToMark[ id ] = isUnread;
	} );

	ext.notifyme.setReadStatus( notificationsToMark ).done( ( response ) => {
		const notifications = response;
		for ( const id in notifications ) {
			const isProcessed = notifications[ id ];
			if ( !isProcessed ) {
				console.error( 'Failed to change read status of notification: ' + id ); // eslint-disable-line no-console
			}
		}

		this.emit( 'groupMarked' );
	} );
};
