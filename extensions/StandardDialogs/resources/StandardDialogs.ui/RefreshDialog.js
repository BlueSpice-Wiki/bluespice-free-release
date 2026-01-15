StandardDialogs = StandardDialogs || {};
StandardDialogs.ui = StandardDialogs.ui || {};

StandardDialogs.ui.RefreshDialog = function StandardDialogsUiRefreshDialog( config ) {
	StandardDialogs.ui.RefreshDialog.super.call( this, config );
};
OO.inheritClass( StandardDialogs.ui.RefreshDialog, StandardDialogs.ui.BaseDialog );

StandardDialogs.ui.RefreshDialog.static.name = 'ext-standard-dialogs-refresh';

StandardDialogs.ui.RefreshDialog.prototype.makeSetupProcessData = function () {
	const data = StandardDialogs.ui.RefreshDialog.super.prototype.makeSetupProcessData.call( this );
	data.title = mw.message( 'standarddialogs-purge-title', this.getDialogTitlePageName() ).plain();

	return data;
};

StandardDialogs.ui.RefreshDialog.prototype.getPrimaryActionLabel = function () {
	return mw.message( 'standarddialogs-purge-btn-label' ).plain();
};

StandardDialogs.ui.RefreshDialog.prototype.getFormItems = function () {
	this.mainInput = this.targetTitle = new OO.ui.LabelWidget( {
		label: mw.message( 'confirm-purge-bottom' ).plain()
	} );

	return [
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.targetTitle, {} )
			],
			label: mw.message( 'confirm-purge-top' ).plain(),
			align: 'top'
		} )
	];
};

StandardDialogs.ui.RefreshDialog.prototype.makeDoneActionProcess = function () {
	const dialog = this;

	const dfd = new $.Deferred();
	mw.loader.using( 'mediawiki.api' ).done( () => {
		const mwApi = new mw.Api();
		mwApi.postWithToken( 'csrf', {
			action: 'purge',
			titles: dialog.pageName
		} ).done( function () {
			dfd.resolve.apply( dialog, arguments );
		} ).fail( function () {
			dfd.reject.apply( dialog, [ new OO.ui.Error( arguments[ 0 ], { recoverable: false } ) ] );
		} );
	} ).fail( function () {
		dfd.reject.apply( dialog, arguments );
	} );

	return new OO.ui.Process( dfd.promise(), this );
};
