StandardDialogs = StandardDialogs || {};
StandardDialogs.ui = StandardDialogs.ui || {};

StandardDialogs.ui.DeleteDialog = function StandardDialogsUiDeleteDialog( config ) {
	StandardDialogs.ui.DeleteDialog.super.call( this, config );
	this.msg = config.msg;
};
OO.inheritClass( StandardDialogs.ui.DeleteDialog, StandardDialogs.ui.BaseDialog );

StandardDialogs.ui.DeleteDialog.static.name = 'ext-standard-dialogs-delete';

StandardDialogs.ui.DeleteDialog.prototype.makeSetupProcessData = function () {
	const data = StandardDialogs.ui.DeleteDialog.super.prototype.makeSetupProcessData.call( this );
	data.title = mw.message( 'standarddialogs-delete-title', this.getDialogTitlePageName() ).plain();
	data.actions[ 0 ].flags[ 1 ] = 'destructive';

	return data;
};

StandardDialogs.ui.DeleteDialog.prototype.getFormItems = function () {
	this.mainInput = this.reasonCombo = new OO.ui.TextInputWidget( {
		id: this.elementId + '-cbx-reason'
	} );
	this.otherReasonText = new OO.ui.TextInputWidget( {
		id: this.elementId + '-tf-reason'
	} );
	this.watchCheckbox = new OO.ui.CheckboxInputWidget( {
		id: this.elementId + '-cb-watch',
		value: 'watch',
		selected: true
	} );
	this.deleteSubpagesCheckbox = new OO.ui.CheckboxInputWidget( {
		id: this.elementId + '-cb-delete-subpages',
		value: 'delete-subpages',
		selected: true
	} );

	return [
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.Element( {
					content: [ new OO.ui.HtmlSnippet( this.msg.confirmdeletetext ) ]
				} ),
				new OO.ui.FieldLayout( this.reasonCombo, {
					label: mw.message( 'deletecomment' ).plain(),
					align: 'top'
				} ),
				new OO.ui.FieldLayout( this.otherReasonText, {
					label: mw.message( 'deleteotherreason' ).plain(),
					align: 'top'
				} ),
				new OO.ui.FieldLayout( this.watchCheckbox, {
					label: mw.message( 'watchthis' ).plain(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.deleteSubpagesCheckbox, {
					label: mw.message( 'standarddialogs-delete-subpages' ).plain(),
					align: 'inline'
				} )
			]
		} )
	];
};

StandardDialogs.ui.DeleteDialog.prototype.makeDoneActionProcess = function () {
	const dialog = this;
	const dfd = new $.Deferred();
	mw.loader.using( 'mediawiki.api' ).done( () => {
		const mwApi = new mw.Api();
		const getPagesToDeletePromise = dialog.getPagesToDelete( dialog.pageName, mwApi );
		getPagesToDeletePromise.done( ( pages ) => {
			if ( pages.length > 10 ) {
				dialog.confirmMassDeletion( pages.length ).done( () => {
					dialog.performDeletion( dfd, pages, mwApi );
				} )
					.fail( function () {
						dfd.reject.apply( dialog, arguments );
					} );
			} else {
				dialog.performDeletion( dfd, pages, mwApi );
			}
		} );
	} ).fail( function () {
		dfd.reject.apply( dialog, arguments );
	} );

	return new OO.ui.Process( dfd.promise(), this );
};

StandardDialogs.ui.DeleteDialog.prototype.confirmMassDeletion = function ( numberOfPages ) {
	const dfd = new $.Deferred();
	const confirmMessage = mw.message(
		'standarddialogs-confirm-mass-deletion', numberOfPages ).text();
	OO.ui.confirm( confirmMessage ).done( ( confirmed ) => {
		if ( confirmed ) {
			dfd.resolve();
		} else {
			dfd.reject();
		}
	} );
	return dfd.promise();
};

StandardDialogs.ui.DeleteDialog.prototype.performDeletion = function ( dfd, pages, mwApi ) {
	const dialog = this;
	const deletePageApiCallPromises = [];
	const params = {
		action: 'delete',
		reason: this.reasonCombo.getValue() + this.otherReasonText.getValue()
	};
	if ( dialog.watchCheckbox.isSelected() ) {
		params.watchlist = 'watch';
	} else {
		params.watchlist = 'unwatch';
	}
	for ( let i = 0; i < pages.length; i++ ) {
		params.title = pages[ i ];
		const deletePageApiCallPromise = mwApi.postWithToken(
			'csrf',
			// pass a copy of params to avoid overwriting the title
			Object.assign( {}, params )
		);
		deletePageApiCallPromises.push( deletePageApiCallPromise );
	}
	$.when.apply( $, deletePageApiCallPromises ).done( function () {
		dfd.resolve.apply( this, arguments );
	} ).fail( function () {
		const error = new OO.ui.Error( arguments[ 0 ], { recoverable: false } );
		dfd.reject.apply( this, [ error ] );
	} );
};

StandardDialogs.ui.DeleteDialog.prototype.getPagesToDelete = function ( pageName, mwApi ) {
	const dfd = new $.Deferred();
	const title = mw.Title.newFromText( pageName );
	let pagesToDelete = [ pageName ];
	if ( this.deleteSubpagesCheckbox.isSelected() ) {
		const getSubpagesPromise = this.getSubpages( title, mwApi );
		getSubpagesPromise.done( ( subpages ) => {
			pagesToDelete = pagesToDelete.concat( subpages );
			dfd.resolve( pagesToDelete );
		} ).fail( function () {
			dfd.reject.apply( this, arguments );
		} );
	} else {
		dfd.resolve( pagesToDelete );
	}

	return dfd.promise();
};

StandardDialogs.ui.DeleteDialog.prototype.getSubpages = function ( title, mwApi, contd ) {
	const dialog = this;
	const dfd = new $.Deferred();
	const params = {
		action: 'query',
		list: 'allpages',
		apnamespace: title.getNamespaceId(),
		apfilterredir: 'nonredirects',
		apprefix: title.getMainText() + '/',
		apdir: 'ascending',
		aplimit: 'max',
		approp: 'info'
	};
	if ( contd ) {
		params.apcontinue = contd;
	}
	mwApi.get( params ).done( ( data ) => {
		const subpages = [];
		if ( data.query.allpages ) {
			for ( let i = 0; i < data.query.allpages.length; i++ ) {
				subpages.push( data.query.allpages[ i ].title );
			}
		}
		if ( data.continue ) {
			const getSubpagesPromise = dialog.getSubpages( title, mwApi, data.continue.apcontinue );
			getSubpagesPromise.done( ( subpages2 ) => {
				dfd.resolve( subpages.concat( subpages2 ) );
			} ).fail( function () {
				dfd.reject.apply( dialog, arguments );
			} );
		} else {
			dfd.resolve( subpages );
		}
	} ).fail( function () {
		dfd.reject.apply( dialog, arguments );
	} );

	return dfd.promise();
};
