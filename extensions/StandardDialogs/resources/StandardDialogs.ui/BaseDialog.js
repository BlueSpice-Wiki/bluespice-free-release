StandardDialogs = window.StandardDialogs || {};
StandardDialogs.ui = StandardDialogs.ui || {};

StandardDialogs.ui.BaseDialog = function StandardDialogsUiBaseDialog( config ) {
	StandardDialogs.ui.BaseDialog.super.call( this, config );
	this.pageName = config.pageName;
	this.mainInput = null;
};
OO.inheritClass( StandardDialogs.ui.BaseDialog, OO.ui.ProcessDialog );

StandardDialogs.ui.BaseDialog.prototype.getDialogTitlePageName = function () {
	return this.pageName.replace( '_', ' ' );
};

StandardDialogs.ui.BaseDialog.prototype.getSetupProcess = function ( data ) {
	data = data || {};
	const additionalData = this.makeSetupProcessData();
	data = Object.assign( data, additionalData );
	return StandardDialogs.ui.BaseDialog.super.prototype.getSetupProcess.call( this, data );
};

StandardDialogs.ui.BaseDialog.prototype.initialize = function () {
	StandardDialogs.ui.BaseDialog.super.prototype.initialize.call( this );
	this.content = new OO.ui.PanelLayout( {
		padded: true,
		expanded: true
	} );
	const formItems = this.getFormItems();
	this.content.$element.append(
		formItems.map( ( item ) => item.$element )
	);
	this.$body.append( this.content.$element );
};

StandardDialogs.ui.BaseDialog.prototype.getReadyProcess = function () {
	if ( this.mainInput ) {
		if ( this.mainInput.focus ) {
			this.mainInput.focus();
		}
		this.mainInput.connect( this, {
			enter: function () {
				this.executeAction( 'done' );
			}
		} );
	}
	return new OO.ui.Process( () => {} );
};

StandardDialogs.ui.BaseDialog.prototype.makeSetupProcessData = function () {
	return {
		actions: [
			{
				action: 'done',
				label: this.getPrimaryActionLabel(),
				flags: [ 'primary', 'progressive' ],
				id: this.elementId + '-btn-done'
			},
			{
				title: mw.message( 'standarddialogs-btn-cancel' ).plain(),
				flags: [ 'safe', 'close' ],
				id: this.elementId + '-btn-cancel'
			}
		]
	};
};

StandardDialogs.ui.BaseDialog.prototype.getPrimaryActionLabel = function () {
	return mw.message( 'standarddialogs-btn-done' ).plain();
};

// Stub to be overwritten by subclass
StandardDialogs.ui.BaseDialog.prototype.getFormItems = function () {
	return [];
};

StandardDialogs.ui.BaseDialog.prototype.show = function () {
	if ( !this.windowManager ) {
		this.windowManager = new OO.ui.WindowManager( {
			modal: true
		} );
		$( document.body ).append( this.windowManager.$element );
		this.windowManager.addWindows( [ this ] );
	}

	this.windowManager.openWindow( this );
};

StandardDialogs.ui.BaseDialog.prototype.getBodyHeight = function () {
	if ( !this.$errors.hasClass( 'oo-ui-element-hidden' ) ) {
		return this.$element.find( '.oo-ui-processDialog-errors' )[ 0 ].scrollHeight;
	}

	return this.$element.find( '.oo-ui-window-body' )[ 0 ].scrollHeight + 10;
};

StandardDialogs.ui.BaseDialog.prototype.getActionProcess = function ( action ) {
	if ( action === 'done' ) {
		const doneActionProcess = this.makeDoneActionProcess();
		doneActionProcess.next( this.onActionDone, this );
		return doneActionProcess;
	}
	return StandardDialogs.ui.BaseDialog.super.prototype.getActionProcess.call( this, action );
};

// Stub to be overwritten by subclass
StandardDialogs.ui.BaseDialog.prototype.makeDoneActionProcess = function () {
	return new OO.ui.Process( ( () => {} ), this );
};

// Stub to be overwritten by subclass
StandardDialogs.ui.BaseDialog.prototype.onActionDone = function ( action ) {
	let args = [ 'actioncompleted' ];
	args = args.concat( this.getActionCompletedEventArgs() );
	this.emit.apply( this, args );
	this.close( { action: action } );
};

// Stub to be overwritten by subclass
StandardDialogs.ui.BaseDialog.prototype.getActionCompletedEventArgs = function () {
	return [];
};

StandardDialogs.ui.BaseDialog.prototype.validateTitleNotExist = function ( value ) {
	this.clearError();
	if ( !value ) {
		this.actions.setAbilities( { done: false } );
		return;
	}
	new mw.Api().get( {
		action: 'query',
		prop: 'pageprops',
		titles: value
	} ).done( ( data ) => {
		// Check if there is data.query.pages.-1
		if ( data.query && data.query.pages && data.query.pages[ -1 ] ) {
			// eslint-disable-next-line no-prototype-builtins
			if ( data.query.pages[ -1 ].hasOwnProperty( 'invalid' ) ) {
				this.actions.setAbilities( { done: false } );
				this.setError( data.query.pages[ -1 ].invalidreason );
			} else {
				this.actions.setAbilities( { done: true } );
			}
		} else {
			this.actions.setAbilities( { done: false } );
			this.setExistWarning();
		}
	} ).fail( () => {
		// Something went wrong, let user go to the page and deal with it there
		this.actions.setAbilities( { done: true } );
	} );
};

StandardDialogs.ui.BaseDialog.prototype.setError = function ( error ) {
	if ( this.mainFieldset ) {
		this.mainFieldset.setErrors( [ error ] );
	}
	if ( this.mainInput.lookupMenu ) {
		this.mainInput.lookupMenu.toggle( false );
	}
	this.updateSize();
};

StandardDialogs.ui.BaseDialog.prototype.clearError = function () {
	this.mainFieldset.setWarnings( [] );
	this.mainFieldset.setErrors( [] );
	this.updateSize();
};

StandardDialogs.ui.BaseDialog.prototype.setExistWarning = function () {
	if ( this.mainFieldset ) {
		this.mainFieldset.setWarnings( [ mw.message( 'standarddialogs-validation-page-exist-info-label' ).text() ] );
	}
	this.updateSize();
};
