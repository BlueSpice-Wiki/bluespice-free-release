window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.dialog = window.enhancedUpload.ui.dialog || {};

enhancedUpload.ui.dialog.DetailsDialog = function enhancedUploadUiDialogDetailsDialog( cfg ) {
	cfg = cfg || {};
	enhancedUpload.ui.dialog.DetailsDialog.super.call( this, cfg );

	this.defaultCategories = cfg.categories || '';
	this.defaultPrefix = cfg.prefix;
	this.defaultDescription = cfg.description || '';
};

OO.inheritClass( enhancedUpload.ui.dialog.DetailsDialog, OO.ui.ProcessDialog );

enhancedUpload.ui.dialog.DetailsDialog.static.name = 'details-dialog';

enhancedUpload.ui.dialog.DetailsDialog.static.title =
	mw.message( 'enhancedupload-attachments-media-dialog-details-settings' ).plain();

enhancedUpload.ui.dialog.DetailsDialog.static.actions = [
	{
		title: mw.message( 'enhancedupload-attachments-media-dialog-details-cancel' ).plain(),
		icon: 'close',
		flags: 'safe'
	},
	{
		label: mw.message( 'enhancedupload-attachments-media-dialog-details-upload' ).plain(),
		action: 'upload',
		flags: [ 'primary', 'progressive' ]
	}
];

enhancedUpload.ui.dialog.DetailsDialog.prototype.getSetupProcess = function () {
	return enhancedUpload.ui.dialog.DetailsDialog.super.prototype.getSetupProcess.call( this );
};

enhancedUpload.ui.dialog.DetailsDialog.prototype.getBodyHeight = function () {
	// eslint-disable-next-line no-jquery/no-class-state
	if ( !this.$errors.hasClass( 'oo-ui-element-hidden' ) ) {
		return this.$element.find( '.oo-ui-processDialog-errors' )[ 0 ].scrollHeight;
	}

	return this.$element.find( '.oo-ui-window-body' )[ 0 ].scrollHeight + 10;
};

enhancedUpload.ui.dialog.DetailsDialog.prototype.initialize = function () {
	enhancedUpload.ui.dialog.DetailsDialog.super.prototype.initialize.call( this );
	this.content = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	this.details = new enhancedUpload.ui.widget.DetailsUploadWidget( {
		categories: this.defaultCategories,
		description: this.defaultDescription
	} );
	this.content.$element.append( this.details.$element );
	this.$body.append( this.content.$element );
};

enhancedUpload.ui.dialog.DetailsDialog.prototype.show = function () {
	if ( !this.windowManager ) {
		this.windowManager = new OO.ui.WindowManager( {
			modal: true
		} );
		$( document.body ).append( this.windowManager.$element );
		this.windowManager.addWindows( [ this ] );
	}
	this.windowManager.openWindow( this );
};

enhancedUpload.ui.dialog.DetailsDialog.prototype.getActionProcess = function ( action ) {
	let desc, cat, descAndCatText, args;
	if ( action === 'upload' ) {
		return new OO.ui.Process( () => {
			desc = this.details.getDescription();
			cat = this.details.getCategories();
			descAndCatText = desc + ' ' + cat;

			args = [ 'detailscompleted', descAndCatText ];
			this.emit.apply( this, args );
			this.close();

		} );
	}
	return enhancedUpload.ui.dialog.DetailsDialog.super.prototype.getActionProcess.call(
		this, action
	);
};
