ext.notifyme.ui.subscriptionset.Watchlist = function ( cfg ) { // eslint-disable-line no-unused-vars
	// Parent constructor
	ext.notifyme.ui.subscriptionset.Watchlist.parent.apply( this, arguments );
};

OO.inheritClass( ext.notifyme.ui.subscriptionset.Watchlist, ext.notifyme.ui.SubscriptionSet );

ext.notifyme.ui.subscriptionset.Watchlist.prototype.getLabel = function () {
	// STUB
	return mw.message( 'notifyme-ui-subscriptionset-watchlist-label' ).text();
};

ext.notifyme.ui.subscriptionset.Watchlist.prototype.getKey = function () {
	// STUB
	return 'watchlist';
};

ext.notifyme.ui.subscriptionset.Watchlist.prototype.getEditor = function ( dialog ) { // eslint-disable-line no-unused-vars
	return null;
};

ext.notifyme.ui.subscriptionset.Watchlist.prototype.getHeaderKeyValue = function () {
	return '';
};

ext.notifyme.subscriptionSetRegistry.register( 'watchlist', ext.notifyme.ui.subscriptionset.Watchlist );
