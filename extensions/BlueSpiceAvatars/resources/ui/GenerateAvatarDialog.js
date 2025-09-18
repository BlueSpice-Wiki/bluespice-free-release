bs.util.registerNamespace( 'bs.avatars.ui' );

bs.avatars.ui.GenerateAvatarDialog = function ( cfg ) {
	cfg = cfg || {};
	bs.avatars.ui.GenerateAvatarDialog.parent.call( this, Object.assign( {
		size: 'medium'
	}, cfg ) );

	this.$element.addClass( 'bs-avatars-generate-avatar-dialog' );
};

OO.inheritClass( bs.avatars.ui.GenerateAvatarDialog, OO.ui.ProcessDialog );

bs.avatars.ui.GenerateAvatarDialog.static.name = 'BSGenerateAvatarDialog';
bs.avatars.ui.GenerateAvatarDialog.static.title = mw.message( 'bs-avatars-generate-new-label' ).text();
bs.avatars.ui.GenerateAvatarDialog.static.actions = [
	{
		action: 'generate',
		label: mw.msg( 'bs-avatars-generate-button' ),
		flags: [ 'primary', 'progressive' ]
	},
	{
		action: 'cancel',
		flags: [ 'safe', 'close' ],
		title: mw.msg( 'bs-avatars-generic-cancel' )
	}
];

bs.avatars.ui.GenerateAvatarDialog.prototype.initialize = function () {
	bs.avatars.ui.GenerateAvatarDialog.parent.prototype.initialize.call( this );

	this.panel = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true
	} );
	this.panel.$element.append( new OO.ui.LabelWidget( {
		label: mw.msg( 'bs-avatars-generate-description' )
	} ).$element );
	this.$body.append( this.panel.$element );
};

bs.avatars.ui.GenerateAvatarDialog.prototype.getActionProcess = function ( action ) {
	return bs.avatars.ui.GenerateAvatarDialog.parent.prototype.getActionProcess.call( this, action ).next(
		function () {
			if ( action === 'generate' ) {
				const dfd = $.Deferred();
				new mw.Api().postWithToken( 'csrf', {
					action: 'bs-avatars-tasks',
					task: 'generateAvatar'
				} ).done( ( r ) => {
					if ( r.hasOwnProperty( 'success' ) && r.success ) {
						this.close( { reload: true } );
					} else {
						dfd.reject();
					}
				} ).fail( () => {
					dfd.reject();
				} );
				return dfd.promise();
			}
			if ( action === 'cancel' ) {
				this.close();
			}
		}, this
	);
};
