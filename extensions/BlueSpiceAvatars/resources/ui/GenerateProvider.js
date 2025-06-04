bs.util.registerNamespace( 'bs.avatars.ui' );

bs.avatars.ui.GenerateProvider = function () {
	bs.avatars.ui.GenerateProvider.parent.call( this );
};

OO.inheritClass( bs.avatars.ui.GenerateProvider, ext.userProfile.ui.ProfileImageProvider );

bs.avatars.ui.GenerateProvider.prototype.getLabel = function () {
	return mw.message( 'bs-avatars-generate-new-label' ).text();
};

bs.avatars.ui.GenerateProvider.prototype.getDialog = function () {
	return new bs.avatars.ui.GenerateAvatarDialog( {} );
};

ext.userProfile.profileImage.providerRegistry.register( 'GenerateProvider', new bs.avatars.ui.GenerateProvider() );
