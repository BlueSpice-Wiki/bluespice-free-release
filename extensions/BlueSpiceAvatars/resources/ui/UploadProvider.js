bs.util.registerNamespace( 'bs.avatars.ui' );

bs.avatars.ui.UploadProvider = function () {
	bs.avatars.ui.UploadProvider.parent.call( this );
};

OO.inheritClass( bs.avatars.ui.UploadProvider, ext.userProfile.ui.ProfileImageProvider );

bs.avatars.ui.UploadProvider.prototype.getLabel = function () {
	return mw.msg( 'bs-avatars-file-upload-fieldset-title' );
};

bs.avatars.ui.UploadProvider.prototype.getDialog = function () {
	return new bs.avatars.ui.UploadImageDialog( {} );
};

ext.userProfile.profileImage.providerRegistry.register( 'UploadProvider', new bs.avatars.ui.UploadProvider() );
