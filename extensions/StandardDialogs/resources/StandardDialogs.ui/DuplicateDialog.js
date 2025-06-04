StandardDialogs = StandardDialogs || {};
StandardDialogs.ui = StandardDialogs.ui || {};

StandardDialogs.ui.DuplicateDialog = function StandardDialogsUiDuplicateDialog( config ) {
	StandardDialogs.ui.DuplicateDialog.super.call( this, config );
	this.page = new mw.Title.newFromText( this.pageName ); // eslint-disable-line new-cap
	this.subpages = [];
	this.talkpages = [];
};
OO.inheritClass( StandardDialogs.ui.DuplicateDialog, StandardDialogs.ui.BaseDialog );

StandardDialogs.ui.DuplicateDialog.static.name = 'ext-standard-dialogs-duplicate';

StandardDialogs.ui.DuplicateDialog.prototype.makeSetupProcessData = function () {
	const data = StandardDialogs.ui.DuplicateDialog.super.prototype.makeSetupProcessData.call( this );
	data.title = mw.message( 'standarddialogs-copy-title', this.getDialogTitlePageName() ).plain();

	return data;
};

StandardDialogs.ui.DuplicateDialog.prototype.getFormItems = function () {
	this.mainInput = this.targetTitle = new OOJSPlus.ui.widget.TitleInputWidget( {
		id: this.elementId + '-tf-target',
		value: this.getDialogTitlePageName() + ' (2)',
		$overlay: this.$overlay,
		mustExist: false,
		contentPagesOnly: false
	} );
	this.mainInput.connect( this, {
		change: 'onTitleChange'
	} );
	this.checkDiscussion = new OO.ui.CheckboxInputWidget( {
		id: this.elementId + '-cb-discussion',
		value: 'discussion',
		selected: false
	} );
	this.checkSubpages = new OO.ui.CheckboxInputWidget( {
		id: this.elementId + '-cb-subpages',
		value: 'subpages',
		selected: false
	} );

	this.mainFieldset = new OO.ui.FieldLayout( this.mainInput, {
		label: mw.message( 'standarddialogs-copy-page-new' ).plain(),
		align: 'top'
	} );
	return [
		new OO.ui.FieldsetLayout( {
			items: [
				this.mainFieldset,
				new OO.ui.FieldLayout( this.checkDiscussion, {
					label: mw.message( 'standarddialogs-copy-discussion' ).plain(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.checkSubpages, {
					label: mw.message( 'standarddialogs-copy-subpages' ).plain(),
					align: 'inline'
				} )
			]
		} )
	];
};

StandardDialogs.ui.DuplicateDialog.prototype.onTitleChange = function ( value ) {
	if ( this.typeTimeout ) {
		clearTimeout( this.typeTimeout );
	}
	this.typeTimeout = setTimeout( () => {
		this.validateTitleNotExist( value );
	}, 500 );
};

StandardDialogs.ui.DuplicateDialog.prototype.makeDoneActionProcess = function () {
	const me = this;
	this.newTitle = mw.Title.newFromText( me.targetTitle.getValue() );
	const dfd = new $.Deferred();
	mw.loader.using( 'mediawiki.api' ).done( () => {
		const dfdCopy = me.doCopy( me.pageName, me.targetTitle.getValue() );
		$.when( dfdCopy ).done( () => {
			let dfdDiscussion, dfdSubpages;

			if ( me.checkDiscussion.isSelected() ) {
				dfdDiscussion = me.getDiscussionPages( me.page.getName(), me.page.getNamespaceId() );
			}

			if ( me.checkSubpages.isSelected() ) {
				dfdSubpages = me.getSubPages( me.page.getName(), me.page.getNamespaceId() );
			}

			$.when( dfdDiscussion, dfdSubpages ).done( () => {
				const copyDfds = [];
				const mainTargetPageName = me.targetTitle.getValue().replace( / /g, '_' );
				if ( me.subpages.length > 0 ) {
					me.subpages.forEach( ( subpage ) => {
						const sourceName = subpage.replace( / /g, '_' ),
							targetName = sourceName.replace( me.pageName, mainTargetPageName ),
							currentCopyDfd = me.doCopy( sourceName, targetName );
						copyDfds.push( currentCopyDfd );
					} );
				}
				if ( me.talkpages.length > 0 ) {
					me.talkpages.forEach( ( talkpage ) => {
						const sourceName = talkpage.replace( / /g, '_' ),
							targetName = sourceName.replace( me.pageName, mainTargetPageName ),
							currentCopyDfd = me.doCopy( sourceName, targetName );
						copyDfds.push( currentCopyDfd );
					} );
				}
				$.when.apply( me, copyDfds ).done( function () {
					dfd.resolve.apply( me, arguments );
				} );
			} );
		} );
	} ).fail( function () {
		dfd.reject.apply( me, arguments );
	} );

	return new OO.ui.Process( dfd.promise(), this );
};

StandardDialogs.ui.DuplicateDialog.prototype.getActionCompletedEventArgs = function () {
	return [ this.newTitle ];
};

StandardDialogs.ui.DuplicateDialog.prototype.getDiscussionPages = function ( srcPageName, srcNamespace ) {
	const me = this;
	const dfd = new $.Deferred();
	const mwApi = new mw.Api();
	mwApi.postWithToken( 'csrf', {
		action: 'query',
		list: 'allpages',
		apprefix: srcPageName,
		apnamespace: srcNamespace + 1
	} ).done( ( resp ) => {
		me.talkpages.push( resp.query.allpages[ 0 ].title );
		dfd.resolve( resp );
	} ).fail( ( error ) => {
		dfd.reject( error );
	} );
	if ( me.checkSubpages.isSelected() ) {
		mwApi.postWithToken( 'csrf', {
			action: 'query',
			list: 'allpages',
			apprefix: srcPageName + '/',
			apnamespace: srcNamespace + 1
		} ).done( ( resp ) => {
			resp.query.allpages.forEach( ( page ) => {
				me.talkpages.push( page.title );
			} );
			dfd.resolve( resp );
		} ).fail( ( error ) => {
			dfd.reject( error );
		} );
	}
	return dfd.promise();
};

StandardDialogs.ui.DuplicateDialog.prototype.getSubPages = function ( srcPageName, srcNamespace ) {
	const me = this;

	const dfd = me.doGetSubPages( srcPageName, srcNamespace, '' );
	return dfd.promise();
};

StandardDialogs.ui.DuplicateDialog.prototype.doGetSubPages = function ( srcPageName, srcNamespace, continueVal ) {
	const me = this;
	const dfd = new $.Deferred();
	const params = {
		action: 'query',
		list: 'allpages',
		apprefix: srcPageName + '/',
		apnamespace: srcNamespace
	};

	if ( continueVal !== '' ) {
		params.apcontinue = continueVal;
	}

	const mwApi = new mw.Api();
	mwApi.postWithToken( 'csrf', params ).done( ( resp ) => {
		resp.query.allpages.forEach( ( page ) => {
			me.subpages.push( page.title );
		} );
		if ( resp.continue ) {
			const recursiveCall = me.doGetSubPages( srcPageName, srcNamespace, resp.continue.apcontinue );
			recursiveCall.done( () => {
				dfd.resolve( resp );
			} );
		} else {
			dfd.resolve( resp );
		}
	} ).fail( ( error ) => {
		dfd.reject( error );
	} );

	return dfd.promise();
};

StandardDialogs.ui.DuplicateDialog.prototype.doCopy = function ( srcPageName, targetPageName ) {
	const dfd = new $.Deferred();
	const mwApi = new mw.Api();
	mwApi.postWithToken( 'csrf', {
		action: 'query',
		titles: srcPageName,
		prop: 'revisions',
		rvprop: 'content',
		indexpageids: ''
	} ).done( ( resp ) => {
		const pageId = resp.query.pageids[ 0 ];
		const pageInfo = resp.query.pages[ pageId ];
		if ( pageInfo.missing || !pageInfo.revisions || !pageInfo.revisions[ 0 ] ) {
			dfd.reject( resp );
		}
		mwApi.postWithToken( 'csrf', {
			action: 'edit',
			title: targetPageName,
			text: pageInfo.revisions[ 0 ][ '*' ],
			continue: ''
		} ).fail( function () {
			dfd.reject( [ new OO.ui.Error( arguments[ 0 ], { recoverable: false } ) ] );
		} )
			.done( function ( resp ) { // eslint-disable-line no-shadow
				if ( !resp.edit.result || resp.edit.result.toLowerCase() !== 'success' ) {
					dfd.reject( resp );
				}

				if ( resp.edit.title === undefined ) {
					dfd.reject( resp );
				}
				dfd.resolve( arguments );
			} );
	} ).fail( function () {
		dfd.reject( [ new OO.ui.Error( arguments[ 0 ], { recoverable: false } ) ] );
	} );
	return dfd.promise();
};
