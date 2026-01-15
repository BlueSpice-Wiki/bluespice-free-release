bs.util.registerNamespace( 'bs.pageassignments.ui.notifications' );

bs.pageassignments.ui.notifications.AssignedPagesSubscriptionSet = function ( cfg ) { // eslint-disable-line no-unused-vars
	// Parent constructor
	bs.pageassignments.ui.notifications.AssignedPagesSubscriptionSet.parent.apply( this, arguments );
};

OO.inheritClass( bs.pageassignments.ui.notifications.AssignedPagesSubscriptionSet, ext.notifyme.ui.SubscriptionSet );

bs.pageassignments.ui.notifications.AssignedPagesSubscriptionSet.prototype.getLabel = function () {
	return mw.message( 'bs-pageassignments-notification-ui-subscriptionset-assigned-pages' ).text();
};

bs.pageassignments.ui.notifications.AssignedPagesSubscriptionSet.prototype.getKey = function () {
	return 'pa-assigned-pages';
};

bs.pageassignments.ui.notifications.AssignedPagesSubscriptionSet.prototype.getEditor = function ( dialog ) { // eslint-disable-line no-unused-vars
	return null;
};

bs.pageassignments.ui.notifications.AssignedPagesSubscriptionSet.prototype.getHeaderKeyValue = function () {
	return '';
};

ext.notifyme.subscriptionSetRegistry.register( 'pa-assigned-pages', bs.pageassignments.ui.notifications.AssignedPagesSubscriptionSet );
