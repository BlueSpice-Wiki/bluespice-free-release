ext.notifyme.ui.subscriptionset.Namespace = function () {
	// Parent constructo
	ext.notifyme.ui.subscriptionset.Namespace.parent.apply( this, arguments );
};

OO.inheritClass( ext.notifyme.ui.subscriptionset.Namespace, ext.notifyme.ui.SubscriptionSet );

ext.notifyme.ui.subscriptionset.Namespace.prototype.getLabel = function () {
	return mw.message( 'notifyme-ui-subscriptionset-namespace-label' ).text();
};

ext.notifyme.ui.subscriptionset.Namespace.prototype.getKey = function () {
	return 'ns';
};

ext.notifyme.ui.subscriptionset.Namespace.prototype.getEditor = function ( dialog ) {
	return new ext.notifyme.ui.subscriptionset.editor.NamespaceSetEditor( { dialog: dialog } );
};

ext.notifyme.ui.subscriptionset.Namespace.prototype.getHeaderKeyValue = function () {
	if ( this.value.set.ns === '0' ) {
		return mw.msg( 'blanknamespace' );
	}
	const labels = mw.config.get( 'wgFormattedNamespaces' );
	return labels.hasOwnProperty( this.value.set.ns ) ? labels[ this.value.set.ns ] : this.value.set.ns;
};

ext.notifyme.subscriptionSetRegistry.register( 'ns', ext.notifyme.ui.subscriptionset.Namespace );
