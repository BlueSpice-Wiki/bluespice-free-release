ext.notifyme.ui.subscriptionset.Category = function () {
	// Parent constructor
	ext.notifyme.ui.subscriptionset.Category.parent.apply( this, arguments );
};

OO.inheritClass( ext.notifyme.ui.subscriptionset.Category, ext.notifyme.ui.SubscriptionSet );

ext.notifyme.ui.subscriptionset.Category.prototype.getLabel = function () {
	return mw.message( 'notifyme-ui-subscriptionset-category-label' ).text();
};

ext.notifyme.ui.subscriptionset.Category.prototype.getKey = function () {
	return 'category';
};

ext.notifyme.ui.subscriptionset.Category.prototype.getEditor = function ( dialog ) {
	return new ext.notifyme.ui.subscriptionset.editor.CategorySetEditor( { dialog: dialog } );
};

ext.notifyme.ui.subscriptionset.Category.prototype.getHeaderKeyValue = function () {
	let text = this.value.set.category;
	// Strip namespace prefix
	const index = text.indexOf( ':' );
	if ( index !== -1 ) {
		text = text.slice( index + 1 );
	}
	return text;
};

ext.notifyme.subscriptionSetRegistry.register( 'category', ext.notifyme.ui.subscriptionset.Category );
