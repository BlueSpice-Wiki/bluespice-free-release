ext.contentdroplets.ui.AllDropletsPage = function ( label, droplets ) {
	ext.contentdroplets.ui.AllDropletsPage.parent.call( this, '_all', label, droplets );
};

OO.inheritClass( ext.contentdroplets.ui.AllDropletsPage,
	ext.contentdroplets.ui.ContentDropletPage );

ext.contentdroplets.ui.AllDropletsPage.prototype.filter = function ( keys ) {
	let key;
	for ( key in this.widgets ) {
		// eslint-disable-next-line no-prototype-builtins
		if ( !this.widgets.hasOwnProperty( key ) ) {
			continue;
		}
		this.widgets[ key ].$element.show();
		if ( keys.indexOf( key ) === -1 ) {
			this.widgets[ key ].$element.hide();
		}
	}
};

ext.contentdroplets.ui.AllDropletsPage.prototype.removeFilter = function () {
	let key;
	for ( key in this.widgets ) {
		// eslint-disable-next-line no-prototype-builtins
		if ( !this.widgets.hasOwnProperty( key ) ) {
			continue;
		}
		this.widgets[ key ].$element.show();
	}
};
