StandardDialogs = StandardDialogs || {};
StandardDialogs.ui = StandardDialogs.ui || {};

StandardDialogs.ui.NewPageDialog = function StandardDialogsUiNewPageDialog( config ) {
	StandardDialogs.ui.NewPageDialog.super.call( this, config );
};
OO.inheritClass( StandardDialogs.ui.NewPageDialog, StandardDialogs.ui.BaseDialog );

StandardDialogs.ui.NewPageDialog.static.name = 'ext-standard-dialogs-new-page';

StandardDialogs.ui.NewPageDialog.prototype.makeSetupProcessData = function () {
	const data = StandardDialogs.ui.NewPageDialog.super.prototype.makeSetupProcessData.call( this );
	data.title = mw.message( 'standarddialogs-new-page-title' ).plain();

	return data;
};
StandardDialogs.ui.NewPageDialog.prototype.getFormItems = function () {
	const title = new mw.Title( this.pageName );
	let prefix = '';
	if ( title.getNamespaceId() > 0 ) {
		prefix = title.getNamespacePrefix();
	}
	this.mainInput = this.targetTitle = new OOJSPlus.ui.widget.TitleInputWidget( {
		id: this.elementId + '-tf-target',
		value: prefix,
		$overlay: this.$overlay,
		mustExist: false,
		contentPagesOnly: false
	} );
	this.mainInput.connect( this, {
		change: 'onTitleChange'
	} );
	this.mainFieldset = new OO.ui.FieldLayout( this.targetTitle, {
		label: mw.message( 'standarddialogs-new-page-label' ).plain(),
		align: 'top'
	} );

	return [ this.mainFieldset ];
};

StandardDialogs.ui.NewPageDialog.prototype.getSetupProcess = function ( data ) {
	return StandardDialogs.ui.NewPageDialog.super.prototype.getSetupProcess.call( this, data ).next( () => {
		this.actions.setAbilities( { done: false } );
	} );
};

StandardDialogs.ui.NewPageDialog.prototype.makeDoneActionProcess = function () {
	this.newTitle = mw.Title.newFromText( this.targetTitle.getValue() );
	return new OO.ui.Process( ( () => {} ), this );
};
StandardDialogs.ui.NewPageDialog.prototype.getActionCompletedEventArgs = function () {
	return [ this.newTitle ];
};

StandardDialogs.ui.NewPageDialog.prototype.onTitleChange = function ( value ) {
	if ( this.typeTimeout ) {
		clearTimeout( this.typeTimeout );
	}
	this.typeTimeout = setTimeout( () => {
		this.validateTitleNotExist( value );
	}, 500 );
};
