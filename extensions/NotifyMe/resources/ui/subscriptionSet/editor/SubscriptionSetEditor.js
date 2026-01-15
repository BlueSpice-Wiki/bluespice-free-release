ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor = function ( cfg ) {
	cfg = cfg || {};
	OO.EventEmitter.call( this );
	this.layout = undefined;
	this.dialog = cfg.dialog || null;
};

OO.initClass( ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor );
OO.mixinClass( ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor, OO.EventEmitter );

ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor.prototype.getLayout = function () {
	if ( this.layout === undefined ) {
		this.layout = this.makeLayout();
	}
	return this.layout;
};

ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor.prototype.makeLayout = function () {
	// STUB - override
	return new OO.ui.FieldsetLayout();
};

ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor.prototype.getValue = function () {
	// STUB
	return {};
};

ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor.prototype.setValue = function ( value ) { // eslint-disable-line no-unused-vars
	// STUB
};

ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor.prototype.getValidity = function () {
	return $.Deferred().resolve().promise();
};

ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor.prototype.setValidityFlag = function ( valid ) { // eslint-disable-line no-unused-vars
	// STUB
};
