StandardDialogs = StandardDialogs || {};
StandardDialogs.ui = StandardDialogs.ui || {};

StandardDialogs.ui.ProtectDialog = function StandardDialogsUiProtectDialog( config ) {
	config.id = 'standarddialogs-dlg-protect';
	StandardDialogs.ui.ProtectDialog.super.call( this, config );
};
OO.inheritClass( StandardDialogs.ui.ProtectDialog, StandardDialogs.ui.BaseDialog );

StandardDialogs.ui.ProtectDialog.static.name = 'ext-standard-dialogs-protect';
StandardDialogs.ui.ProtectDialog.prototype.makeSetupProcessData = function () {
	const data = StandardDialogs.ui.ProtectDialog.super.prototype.makeSetupProcessData.call( this );
	data.title = mw.message( 'standarddialogs-protect-title', this.getDialogTitlePageName() ).plain();

	return data;
};

StandardDialogs.ui.ProtectDialog.prototype.getFormItems = function () {
	const options = this.getStandardExpiryDates();
	const optionsReason = this.getStandardReasons();
	const optionsEdit = options[ 0 ];
	const optionsMove = options[ 1 ];
	this.mainInput = this.editProtect = new OO.ui.ComboBoxInputWidget( {
		id: this.elementId + '-cbx-edit-level',
		value: mw.message( 'protect-default' ).plain(),
		options: [
			{ data: mw.message( 'protect-default' ).plain() },
			{ data: mw.message( 'standarddialogs-protect-level-editor' ).plain() },
			{ data: mw.message( 'protect-level-sysop' ).plain() }
		],
		$overlay: this.$overlay
	} );
	this.moveProtect = new OO.ui.ComboBoxInputWidget( {
		id: this.elementId + '-cbx-move-level',
		value: mw.message( 'protect-default' ).plain(),
		options: [
			{ data: mw.message( 'protect-default' ).plain() },
			{ data: mw.message( 'standarddialogs-protect-level-editor' ).plain() },
			{ data: mw.message( 'protect-level-sysop' ).plain() }
		],
		disabled: true,
		$overlay: this.$overlay
	} );

	this.protectExpiry = new OO.ui.DropdownWidget( {
		id: this.elementId + '-dd-expiry-edit',
		menu: { items: optionsEdit }
	} );
	this.protectExpiryMove = new OO.ui.DropdownWidget( {
		id: this.elementId + '-dd-expiry-move',
		menu: { items: optionsMove },
		disabled: true
	} );
	this.protectExpiryOther = new OO.ui.TextInputWidget( {
		id: this.elementId + '-tf-expiry-edit-other'
	} );
	this.protectExpiryOtherMove = new OO.ui.TextInputWidget( {
		id: this.elementId + '-tf-expiry-move-other',
		disabled: true
	} );
	this.protectPermissions = new OO.ui.CheckboxInputWidget( {
		id: this.elementId + '-cb-protect-permissons',
		value: 'unchain',
		selected: false
	} );

	this.protectPermissions.connect( this, {
		change: function ( value ) {
			if ( value === true ) {
				this.moveProtect.setDisabled( false );
				this.protectExpiryMove.setDisabled( false );
				this.protectExpiryOtherMove.setDisabled( false );
			} else {
				this.moveProtect.setDisabled( true );
				this.protectExpiryMove.setDisabled( true );
				this.protectExpiryOtherMove.setDisabled( true );
			}
		}
	} );

	this.protectWatchCheckbox = new OO.ui.CheckboxInputWidget( {
		id: this.elementId + '-cb-protect-watch',
		value: 'watch',
		selected: true
	} );
	this.protectReason = new OO.ui.DropdownWidget( {
		id: this.elementId + '-dd-protect-reason',
		menu: { items: optionsReason }
	} );
	this.protectReasonOther = new OO.ui.TextInputWidget( {
		id: this.elementId + '-tf-protect-reason-other'
	} );

	return [
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.editProtect, {
					label: mw.message( 'restriction-edit' ).plain(),
					align: 'top'
				} ),
				new OO.ui.FieldLayout( this.protectExpiry, {
					label: mw.message( 'protectexpiry' ).plain(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.protectExpiryOther, {
					label: mw.message( 'protect-othertime' ).plain(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.protectPermissions, {
					label: mw.message( 'protect-unchain-permissions' ).plain(),
					align: 'inline'
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.moveProtect, {
					label: mw.message( 'restriction-move' ).plain(),
					align: 'top'
				} ),
				new OO.ui.FieldLayout( this.protectExpiryMove, {
					label: mw.message( 'protectexpiry' ).plain(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.protectExpiryOtherMove, {
					label: mw.message( 'protect-othertime' ).plain(),
					align: 'inline'
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.protectReason, {
					label: mw.message( 'protectcomment' ).plain(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.protectReasonOther, {
					label: mw.message( 'protect-otherreason' ).plain(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.protectWatchCheckbox, {
					label: mw.message( 'watchthis' ).plain(),
					align: 'inline'
				} )
			]
		} )
	];
};

StandardDialogs.ui.ProtectDialog.prototype.makeDoneActionProcess = function () {
	const me = this;
	let protections = 'edit=';
	if ( me.editProtect.getValue() === mw.message( 'protect-level-sysop' ).plain() ) {
		protections += 'sysop';
	} else if ( me.editProtect.getValue() === mw.message( 'standarddialogs-protect-level-editor' ).plain() ) {
		protections += 'editor';
	} else {
		protections += 'all';
	}
	let expiries = 'infinite';
	if ( me.protectExpiry.getMenu().findSelectedItem() !== null ) {
		expiries = me.protectExpiry.getMenu().findSelectedItem().data;
	}
	if ( me.moveProtect != '' && me.moveProtect.isDisabled() === false ) { // eslint-disable-line eqeqeq
		protections += '|move=';
		if ( me.moveProtect.getValue() === mw.message( 'protect-level-sysop' ).plain() ) {
			protections += 'sysop';
		} else if ( me.moveProtect.getValue() === mw.message( 'standarddialogs-protect-level-editor' ).plain() ) {
			protections += 'editor';
		} else {
			protections += 'all';
		}

		if ( me.protectExpiryMove.getMenu().findSelectedItem() !== null ) {
			expiries += '| ' + me.protectExpiryMove.getMenu().findSelectedItem().data;
		}
	}

	const dfd = new $.Deferred();
	mw.loader.using( 'mediawiki.api' ).done( () => {
		const mwApi = new mw.Api();
		mwApi.postWithToken( 'csrf', {
			action: 'protect',
			title: me.pageName,
			reason: me.protectReason.getMenu().findSelectedItem() + me.protectReasonOther.getValue(),
			protections: protections,
			expiry: expiries
		} ).done( function () {

			dfd.resolve.apply( me, arguments );
		} ).fail( function () {
			dfd.reject.apply( me, [ new OO.ui.Error( arguments[ 0 ] ) ] );
		} );
	} ).fail( function () {
		dfd.reject.apply( me, arguments );
	} );

	return new OO.ui.Process( dfd.promise(), this );
};

StandardDialogs.ui.ProtectDialog.prototype.getStandardExpiryDates = function () {
	let expiryDates = mw.message( 'protect-expiry-options' ).plain();
	const optionsEdit = [], optionsMove = [];
	expiryDates = expiryDates.split( ',' );
	expiryDates.forEach( ( item ) => {
		optionsEdit.push( new OO.ui.MenuOptionWidget( { label: item.split( ':' )[ 0 ], data: item.split( ':' )[ 1 ] } ) );
		optionsMove.push( new OO.ui.MenuOptionWidget( { label: item.split( ':' )[ 0 ], data: item.split( ':' )[ 1 ] } ) );
	} );
	return [ optionsEdit, optionsMove ];
};

StandardDialogs.ui.ProtectDialog.prototype.getStandardReasons = function () {
	const optionsReason = [];
	let protectReasons = mw.message( 'protect-dropdown' ).plain();
	protectReasons = protectReasons.split( '\n' );

	const otherReason = mw.message( 'protect-otherreason-op' ).plain();
	optionsReason.push( new OO.ui.MenuOptionWidget( { label: otherReason, data: otherReason } ) );
	protectReasons.forEach( ( reason ) => {
		if ( reason.startsWith( '** ' ) ) {
			reason = reason.replace( '** ', '' );
			optionsReason.push( new OO.ui.MenuOptionWidget( { label: reason, data: reason } ) );
		}
	} );

	return optionsReason;
};
