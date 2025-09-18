// I tried hard with toolFactory registration, but
// eventually gave up. This is a hacky but working
// solution.
mw.hook( 've.activationComplete' ).add( () => {
	if ( ve.init.target.$element.hasClass( 've-init-sa-target' ) ) {
		return;
	}
	if ( ve.init.target.$element.hasClass( 've-init-mw-collabTarget' ) ) {
		return;
	}
	const cancelButton = new OO.ui.ButtonWidget(
		{
			label: mw.msg( 'bs-visualeditorconnector-cancel-edit-no-unsaved-changes' ),
			invisibleLabel: true,
			title: mw.msg( 'bs-visualeditorconnector-cancel-edit-no-unsaved-changes' ),
			icon: 'close',
			classes: [ 'bs-visualeditorconnector-cancel-edit' ],
			framed: false
		}
	);

	cancelButton.on( 'click', () => {
		const surface = ve.init.target.getSurface();

		const exit = () => {
			// Disable browser unsaved changes warning in ve.init.mw.DesktopArticleTarget.prototype.onBeforeUnload
			ve.init.target.edited = false;
			window.location.href = mw.util.getUrl();
		};

		if ( ve.init.target.isSaveable() ) {
			// Unsaved changes: show confirmation
			OO.ui.confirm( ve.msg( 'bs-visualeditorconnector-cancel-edit-confirm-message' ) ).done( ( confirmed ) => {
				if ( confirmed ) {
					// User confirmed: discard any unsaved local changes
					surface.getModel().removeDocStateAndChanges();
					exit();
				}
			} );
		} else {
			// No changes: just exit
			exit();
		}
	} );

	// Needed for switching between Visual and Wikitext mode. Otherwise, we get this
	// error: this.items[i].destroy is not a function
	cancelButton.destroy = function () {};

	// Enable cancel button when the text is already dirty on load (happens when
	// there are edits in session storage in the browser).
	ve.init.target.isSaveable() ? cancelButton.setFlags( 'destructive' ) : null; // eslint-disable-line no-unused-expressions

	const items = ve.init.target.getToolbar().items;
	for ( let i = 0; i < items.length; i++ ) {
		if ( !( items[ i ] instanceof OO.ui.ToolGroup ) ) {
			continue;
		}
		for ( let j = 0; j < items[ i ].include.length; j++ ) {
			if (
				items[ i ].include[ j ] === 'save' ||
				(
					typeof items[ i ].include[ j ] === 'object' &&
					items[ i ].include[ j ].hasOwnProperty( 'group' ) &&
					items[ i ].include[ j ].group === 'history'
				)
			) {
				items[ i ].addItems( [ cancelButton ], 0 );
				// Necessary to enable cancel tool
				items[ i ].updateDisabled();
			}
		}
	}

	ve.init.target.cancelButton = cancelButton;
	// This override is dangerous, since it is hard to identify. It shouldn't be
	// necessary; see the comment (copied from the original) why we use it.
	// Putting the override in a better identifyable place outside of the mw.hook
	// context lead to an error, though
	// #Override
	ve.init.mw.ArticleTarget.prototype.updateToolbarSaveButtonState = function () {
		// This should really be an emit('updateState') but that would cause
		// every tool to be updated on every transaction.
		this.toolbarSaveButton.onUpdateState();
		// Added by BlueSpice
		if ( ve.init.target.isSaveable() ) {
			this.cancelButton.setFlags( [ 'destructive' ] );
			this.cancelButton.setLabel( mw.msg( 'bs-visualeditorconnector-cancel-edit' ) );
			this.cancelButton.setTitle( mw.msg( 'bs-visualeditorconnector-cancel-edit' ) );
		} else {
			this.cancelButton.setFlags( { destructive: false } );
			this.cancelButton.setLabel( mw.msg( 'bs-visualeditorconnector-cancel-edit-no-unsaved-changes' ) );
			this.cancelButton.setTitle( mw.msg( 'bs-visualeditorconnector-cancel-edit-no-unsaved-changes' ) );
		}
	};
} );
