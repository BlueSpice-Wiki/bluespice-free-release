bs.util.registerNamespace( 'bs.avatars.ui' );

bs.avatars.ui.UploadImageDialog = function ( cfg ) {
	cfg = cfg || {};
	bs.avatars.ui.UploadImageDialog.parent.call( this, Object.assign( {
		size: 'medium'
	}, cfg ) );

	this.$element.addClass( 'bs-avatars-upload-image-dialog' );
};

OO.inheritClass( bs.avatars.ui.UploadImageDialog, OO.ui.ProcessDialog );

bs.avatars.ui.UploadImageDialog.static.name = 'BSUploadImageDialog';
bs.avatars.ui.UploadImageDialog.static.title = mw.msg( 'bs-avatars-file-upload-fieldset-title' );
bs.avatars.ui.UploadImageDialog.static.actions = [
	{
		action: 'upload',
		label: mw.msg( 'bs-avatars-upload-button' ),
		flags: [ 'primary', 'progressive' ]
	},
	{
		action: 'cancel',
		flags: [ 'safe' ],
		label: mw.msg( 'bs-avatars-generic-cancel' )
	}
];

bs.avatars.ui.UploadImageDialog.prototype.getReadyProcess = function () {
	return bs.avatars.ui.UploadImageDialog.parent.prototype.getReadyProcess.call( this )
		.next( () => {
			this.actions.setAbilities( { upload: false } );
		} );
};

bs.avatars.ui.UploadImageDialog.prototype.initialize = function () {
	bs.avatars.ui.UploadImageDialog.parent.prototype.initialize.call( this );
	this.selector = new OO.ui.SelectFileWidget( {
		multiple: false,
		droppable: true,
		accept: [ 'image/*' ],
		classes: [ 'bs-avatars-upload-selector' ],
		showDropTarget: true
	} );
	this.selector.connect( this, {
		change: 'onFileSelect'
	} );

	this.panel = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true
	} );
	this.panel.$element.append( this.selector.$element );
	this.$body.append( this.panel.$element );
};

bs.avatars.ui.UploadImageDialog.prototype.onFileSelect = function ( file ) {
	if ( file.length > 0 ) {
		file = file[ 0 ];
	} else {
		file = null;
	}
	this.actions.setAbilities( { upload: !!file } );
	this.file = file;
};

bs.avatars.ui.UploadImageDialog.prototype.getActionProcess = function ( action ) {
	return bs.avatars.ui.UploadImageDialog.parent.prototype.getActionProcess.call( this, action ).next(
		function () {
			if ( action === 'upload' ) {
				if ( !this.file ) {
					return;
				}

				const dfd = $.Deferred();
				const url = mw.util.wikiScript( 'api' ) + '?action=bs-avatars-tasks&task=uploadFile';

				// POST multipart/form-data
				const formData = new FormData();
				formData.append( 'avatars', this.file );
				formData.append( 'name', 'avatars' );
				formData.append( 'token', mw.user.tokens.get( 'csrfToken' ) );

				$.ajax( {
					url: url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false
				} ).done( () => {
					this.close( { reload: true } );
				} ).fail( ( err ) => {
					dfd.reject( new OO.ui.Error( err ) );
				} );

				return dfd.promise();
			}

			if ( action === 'cancel' ) {
				this.close();
			}
		}, this
	);
};
