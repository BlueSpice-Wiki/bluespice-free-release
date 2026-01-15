ext.notifyme.ui.ChannelPreferences = function ( cfg ) {
	cfg = cfg || {};
	this.value = {};
	ext.notifyme.ui.ChannelPreferences.parent.call( this, cfg );
	this.$element.addClass( 'ext-notifyme-preference-provider' );
};

OO.inheritClass( ext.notifyme.ui.ChannelPreferences, OO.ui.Widget );

ext.notifyme.ui.ChannelPreferences.prototype.getLayout = function () {
	// STUB
	return undefined;
};

ext.notifyme.ui.ChannelPreferences.prototype.getValue = function () {
	return this.value;
};

ext.notifyme.ui.ChannelPreferences.prototype.setValue = function ( value ) {
	this.value = value;
};
