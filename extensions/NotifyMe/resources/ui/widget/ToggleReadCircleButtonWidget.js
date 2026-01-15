ext.notifyme.ui.widget.ToggleReadCircleButtonWidget = function ( config ) {
	config = config || {};

	// Parent constructor
	ext.notifyme.ui.widget.ToggleReadCircleButtonWidget.super.call( this, config );

	this.$circle = $( '<div>' )
		.addClass( 'notifications-ui-widget-ToggleReadCircleButtonWidget-circle' );
	this.$button.append( this.$circle );

	this.toggleState( config.markAsRead === undefined ? true : !!config.markAsRead );

	this.$element
		.addClass( 'notifications-ui-widget-ToggleReadCircleButtonWidget' );
};

/* Initialization */

OO.inheritClass( ext.notifyme.ui.widget.ToggleReadCircleButtonWidget, OO.ui.ButtonWidget );

/* Methods */

/**
 * Toggle the state of the button from 'mark as read' to 'mark as unread'
 * and vice versa.
 *
 * @param {boolean} [isMarkAsRead] The state is mark as read
 */
ext.notifyme.ui.widget.ToggleReadCircleButtonWidget.prototype.toggleState = function ( isMarkAsRead ) {
	isMarkAsRead = isMarkAsRead === undefined ? !this.markAsRead : !!isMarkAsRead;

	this.markAsRead = isMarkAsRead;

	let titleText;
	if ( this.markAsRead ) {
		titleText = mw.message( 'notifyme-notification-center-mark-as-read-circle-title' ).text();
	} else {
		titleText = mw.message( 'notifyme-notification-center-mark-as-unread-circle-title' ).text();
	}

	this.$circle.toggleClass( 'notifications-ui-widget-ToggleReadCircleButtonWidget-circle-unread', !this.markAsRead );
	this.setTitle( titleText );
};
