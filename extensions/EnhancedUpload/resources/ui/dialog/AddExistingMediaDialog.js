window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.dialog = window.enhancedUpload.ui.dialog || {};

enhancedUpload.ui.dialog.AddExistingMediaDialog = function ( cfg ) {
	enhancedUpload.ui.dialog.AddExistingMediaDialog.super.call( this, cfg );

};

OO.inheritClass( enhancedUpload.ui.dialog.AddExistingMediaDialog, OO.ui.ProcessDialog );

enhancedUpload.ui.dialog.AddExistingMediaDialog.static.name = 'add-media-dialog';
enhancedUpload.ui.dialog.AddExistingMediaDialog.static.title =
	mw.message( 'enhancedupload-attachments-media-dialog-title' ).plain();

enhancedUpload.ui.dialog.AddExistingMediaDialog.static.actions = [
	{
		title: mw.message( 'enhancedupload-attachments-media-dialog-cancel-title' ).plain(),
		icon: 'close',
		flags: 'safe'
	},
	{
		label: mw.message( 'enhancedupload-attachments-media-dialog-add-title' ).plain(),
		action: 'add',
		flags: [ 'primary', 'progressive' ],
		disabled: true
	}
];

enhancedUpload.ui.dialog.AddExistingMediaDialog.prototype.getSetupProcess = function () {
	return enhancedUpload.ui.dialog.AddExistingMediaDialog.super.prototype.getSetupProcess.call(
		this
	);
};

enhancedUpload.ui.dialog.AddExistingMediaDialog.prototype.getBodyHeight = function () {
	// eslint-disable-next-line no-jquery/no-class-state
	if ( !this.$errors.hasClass( 'oo-ui-element-hidden' ) ) {
		return this.$element.find( '.oo-ui-processDialog-errors' )[ 0 ].scrollHeight;
	}

	return this.$element.find( '.oo-ui-window-body' )[ 0 ].scrollHeight + 10;
};

enhancedUpload.ui.dialog.AddExistingMediaDialog.prototype.initialize = function () {
	enhancedUpload.ui.dialog.AddExistingMediaDialog.super.prototype.initialize.call( this );

	this.content = new OO.ui.PanelLayout( {
		expanded: true,
		padded: true
	} );

	this.titleSearch = new OOJSPlus.ui.widget.FileSearchWidget( {
		namespace: 6,
		showImages: true,
		$overlay: this.$overlay
	} );

	this.titleSearch.connect( this, {
		choose: 'activateAdd'
	} );

	const titleField = new OO.ui.FieldsetLayout( {
		expanded: true,
		items: [
			new OO.ui.FieldLayout( this.titleSearch, {
				label: mw.message( 'enhancedupload-attachments-media-dialog-label' ).plain(),
				align: 'top'
			} )
		]
	} );
	this.content.$element.append( titleField.$element );

	this.$body.append( this.content.$element );
};

enhancedUpload.ui.dialog.AddExistingMediaDialog.prototype.show = function () {
	if ( !this.windowManager ) {
		this.windowManager = new OO.ui.WindowManager( {
			modal: true
		} );
		$( document.body ).append( this.windowManager.$element );
		this.windowManager.addWindows( [ this ] );
	}
	this.windowManager.openWindow( this );
};

enhancedUpload.ui.dialog.AddExistingMediaDialog.prototype.getActionProcess = function ( action ) {
	let title, args;
	if ( action === 'add' ) {
		return new OO.ui.Process( () => {
			title = this.titleSearch.getMWTitle();

			args = [ 'actioncompleted', title ];
			this.emit.apply( this, args );
			this.close();

		} );
	}
	return enhancedUpload.ui.dialog.AddExistingMediaDialog.super.prototype.getActionProcess.call(
		this, action
	);
};

enhancedUpload.ui.dialog.AddExistingMediaDialog.prototype.activateAdd = function () {
	this.actions.setAbilities( { add: true } );
};
