StandardDialogs = StandardDialogs || {};
StandardDialogs.widgets = StandardDialogs.widgets || {};

StandardDialogs.widgets.SubpageTitleWidget = function ( cfg ) {
	cfg = cfg || {};
	this.prefix = cfg.prefix;
	StandardDialogs.widgets.SubpageTitleWidget.super.call( this, cfg );
};

OO.inheritClass( StandardDialogs.widgets.SubpageTitleWidget, OOJSPlus.ui.widget.TitleInputWidget );

StandardDialogs.widgets.SubpageTitleWidget.prototype.getLookupCacheDataFromResponse = function ( response ) {
	let path = '';
	for ( const i in response ) {
		const origPrefixed = response[ i ].prefixed;
		if ( origPrefixed.startsWith( this.prefix ) ) {
			path = origPrefixed.slice( this.prefix.length );
		}
		response[ i ].prefixed = path;
	}
	return response;
};
