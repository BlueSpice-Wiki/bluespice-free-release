StandardDialogs = window.StandardDialogs || {};
StandardDialogs.ui = StandardDialogs.ui || {};

StandardDialogs.ui.PageInformationDialog = function StandardDialogsUiPageInformationDialog( config ) {
	StandardDialogs.ui.PageInformationDialog.super.call( this, config );
	this.pageName = config.pageName;
	this.panelRegistry = config.panelRegistry;
	this.pages = [];
};

OO.inheritClass( StandardDialogs.ui.PageInformationDialog, OO.ui.ProcessDialog );

StandardDialogs.ui.PageInformationDialog.static.name = 'ext-standard-dialogs-page-information';
StandardDialogs.ui.PageInformationDialog.static.size = 'large';

StandardDialogs.ui.PageInformationDialog.static.actions = [
	{ action: 'cancel', label: mw.message( 'cancel' ).plain(), flags: [ 'safe', 'close' ] }
];

StandardDialogs.ui.PageInformationDialog.prototype.getSetupProcess = function ( data ) {
	data.title = mw.message(
		'standarddialogs-page-info-title', this.pageName.replace( '_', ' ' )
	).plain();
	let page = null;
	for ( const key in this.panelRegistry.registry ) {
		page = new this.panelRegistry.registry[ key ]( key, {
			pageName: this.pageName
		} );
		this.pages.push( page );
	}

	this.bookletLayout.addPages(
		this.pages
	);
	this.bookletLayout.on( 'set', ( page ) => { // eslint-disable-line no-shadow
		if ( page.onInfoPanelSelect ) {
			page.onInfoPanelSelect();
		}
	} );

	this.bookletLayout.selectFirstSelectablePage();
	return StandardDialogs.ui.PageInformationDialog.super.prototype.getSetupProcess.call( this, data );
};

StandardDialogs.ui.PageInformationDialog.prototype.initialize = function () {
	StandardDialogs.ui.PageInformationDialog.super.prototype.initialize.call( this );
	this.bookletLayout = new OO.ui.BookletLayout( {
		outlined: true,
		expanded: true,
		padded: true
	} );

	this.$body.append( this.bookletLayout.$element );
};

StandardDialogs.ui.PageInformationDialog.prototype.getBodyHeight = function () {
	StandardDialogs.ui.PageInformationDialog.super.prototype.getBodyHeight.call( this );
	if ( !this.$errors.hasClass( 'oo-ui-element-hidden' ) ) {
		return this.$element.find( '.oo-ui-processDialog-errors' )[ 0 ].scrollHeight;
	}
	return 450;
};

StandardDialogs.ui.PageInformationDialog.prototype.getActionProcess = function ( action ) {
	if ( action ) {
		this.close( { action: action } );
	}
	return StandardDialogs.ui.PageInformationDialog.super.prototype.getActionProcess.call( this, action );
};

StandardDialogs.ui.PageInformationDialog.prototype.getTeardownProcess = function () {
	return StandardDialogs.ui.PageInformationDialog.super.prototype.getTeardownProcess.call( this );
};

StandardDialogs.ui.PageInformationDialog.prototype.show = function () {
	if ( !this.windowManager ) {
		this.windowManager = new OO.ui.WindowManager( {
			modal: true
		} );
		$( document.body ).append( this.windowManager.$element );
		this.windowManager.addWindows( [ this ] );
	}

	this.windowManager.openWindow( this );
};
