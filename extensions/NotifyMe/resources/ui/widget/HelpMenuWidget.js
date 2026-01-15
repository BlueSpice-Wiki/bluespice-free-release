ext.notifyme.ui.widget.HelpMenuWidget = function ( cfg ) {
	cfg = cfg || {};

	// Parent constructor
	ext.notifyme.ui.widget.HelpMenuWidget.super.call( this, Object.assign( {
		icon: 'settings',
		label: mw.message( 'notifyme-notification-center-help-menu-aria-label' ).text(),
		indicator: 'down',
		invisibleLabel: true,
		menu: {
			classes: [ 'notifications-ui-widget-HelpMenuWidget-menu' ],
			horizontalPosition: 'end',
			width: 'auto'
		}
	}, cfg ) );

	const prefLink = mw.config.get( 'wgNotificationsSpecialPageLinks' ).preferences;

	this.menu.addItems( [
		new OO.ui.MenuOptionWidget( {
			icon: 'checkAll',
			label: mw.message( 'notifyme-notification-center-help-menu-mark-all-read-label' ).text(),
			data: 'markAllRead'
		} ),
		new OO.ui.MenuOptionWidget( {
			$element: $( '<a>' ).attr( 'href', prefLink ),
			icon: 'settings',
			label: mw.message( 'notifyme-notification-center-help-menu-preferences-label' ).text(),
			data: { href: prefLink }
		} )
	] );

	// Events
	this.menu.connect( this, {
		choose: 'onMenuChoose',
		toggle: 'onMenuToggle'
	} );

	this.$element.addClass( 'notifications-ui-widget-HelpMenuWidget' );
};

OO.inheritClass( ext.notifyme.ui.widget.HelpMenuWidget, OO.ui.ButtonMenuSelectWidget );

/**
 * Handle menu choose events
 *
 * @param {OO.ui.MenuOptionWidget} item Chosen item
 */
ext.notifyme.ui.widget.HelpMenuWidget.prototype.onMenuChoose = function ( item ) {
	const data = item.getData();
	if ( data.href ) {
		location.href = data.href;
	} else if ( data === 'markAllRead' ) {
		this.emit( 'markAllRead' );
	}
};

/**
 * Handle menu toggle events
 *
 * @param {boolean} isVisible If menu is visible currently
 */
ext.notifyme.ui.widget.HelpMenuWidget.prototype.onMenuToggle = function ( isVisible ) {
	if ( isVisible ) {
		this.menu.getVisibleItems()[ 0 ].$element.trigger( 'focus' );
	} else {
		this.$button.trigger( 'focus' );
	}
};
