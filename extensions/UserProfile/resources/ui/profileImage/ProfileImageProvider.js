ext.userProfile.ui.ProfileImageProvider = function() {};

OO.initClass( ext.userProfile.ui.ProfileImageProvider );

ext.userProfile.ui.ProfileImageProvider.prototype.getLabel = function() {
	return '';
};

ext.userProfile.ui.ProfileImageProvider.prototype.getIcon = function() {
	return '';
};

ext.userProfile.ui.ProfileImageProvider.prototype.getDialog = function() {
	return null;
};

ext.userProfile.ui.ProfileImageProvider.prototype.execute = function() {
	const dialog = this.getDialog();
	if ( !dialog ) {
		return;
	}
	OO.ui.getWindowManager().addWindows( [ dialog ] );
	OO.ui.getWindowManager().openWindow( dialog ).closed.then( function( data ) {
		if ( data && data.reload ) {
			window.location.reload();
		}
	} );
};