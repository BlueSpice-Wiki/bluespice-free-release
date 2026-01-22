ve.ui.AttachmentInspector = function VeUiAttachmentInspector( config ) {
	// Parent constructor
	ve.ui.AttachmentInspector.super.call( this, ve.extendObject( { padded: true }, config ) );
};

/* Inheritance */

OO.inheritClass( ve.ui.AttachmentInspector, ve.ui.MWLiveExtensionInspector );

/* Static properties */

ve.ui.AttachmentInspector.static.name = 'attachmentInspector';

ve.ui.AttachmentInspector.static.title = OO.ui.deferMsg( 'enhancedupload-ve-attachment-title' );

ve.ui.AttachmentInspector.static.modelClasses = [ ve.dm.AttachmentNode ];

ve.ui.AttachmentInspector.static.dir = 'ltr';

// This tag does not have any content
ve.ui.AttachmentInspector.static.allowedEmpty = true;
ve.ui.AttachmentInspector.static.selfCloseEmptyBody = false;

/**
 * @inheritdoc
 */
ve.ui.AttachmentInspector.prototype.initialize = function () {
	ve.ui.AttachmentInspector.super.prototype.initialize.call( this );

	// remove input field with links in it
	this.input.$element.remove();

	this.indexLayout = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true
	} );

	this.createFields();

	this.setLayouts();

	// Initialization
	this.$content.addClass( 've-ui-enhancedupload-inspector-content' );

	this.indexLayout.$element.append(
		this.titleLayout.$element,
		this.prefixLayout.$element,
		this.catLayout.$element,
		this.descLayout.$element,
		this.optionsLayout.$element,
		this.versionLayout.$element,
		this.editorLayout.$element,
		this.sizeLayout.$element,
		this.categoriesLayout.$element
	);
	this.form.$element.append(
		this.indexLayout.$element
	);
};

ve.ui.AttachmentInspector.prototype.createFields = function () {
	this.titleInput = new OO.ui.TextInputWidget();
	this.prefixInput = new OO.ui.TextInputWidget();
	this.catInput = new OOJSPlus.ui.widget.CategoryMultiSelectWidget( {
		allowArbitrary: true
	} );
	this.descInput = new OO.ui.MultilineTextInputWidget();
	this.optionsInput = new OO.ui.CheckboxInputWidget();
	this.hideVersion = new OO.ui.CheckboxInputWidget();
	this.hideEditor = new OO.ui.CheckboxInputWidget();
	this.showSize = new OO.ui.CheckboxInputWidget();
	this.showCategories = new OO.ui.CheckboxInputWidget();
};

ve.ui.AttachmentInspector.prototype.setLayouts = function () {
	this.titleLayout = new OO.ui.FieldLayout( this.titleInput, {
		align: 'top',
		label: ve.msg( 'enhancedupload-ve-attachmentinspector-title-label' ),
		help: ve.msg( 'enhancedupload-ve-attachmentinspector-title-help' )
	} );

	this.prefixLayout = new OO.ui.FieldLayout( this.prefixInput, {
		align: 'top',
		label: ve.msg( 'enhancedupload-ve-attachmentinspector-prefix-label' ),
		help: ve.msg( 'enhancedupload-ve-attachmentinspector-prefix-help' )
	} );
	this.catLayout = new OO.ui.FieldLayout( this.catInput, {
		align: 'top',
		label: ve.msg( 'enhancedupload-ve-attachmentinspector-categories' )
	} );
	this.descLayout = new OO.ui.FieldLayout( this.descInput, {
		align: 'top',
		label: ve.msg( 'enhancedupload-ve-attachmentinspector-desc' ),
		help: ''
	} );
	this.optionsLayout = new OO.ui.FieldLayout( this.optionsInput, {
		align: 'inline',
		label: ve.msg( 'enhancedupload-ve-attachmentinspector-options' )
	} );
	this.versionLayout = new OO.ui.FieldLayout( this.hideVersion, {
		align: 'inline',
		label: ve.msg( 'enhancedupload-ve-attachmentinspector-grid-version' )
	} );
	this.editorLayout = new OO.ui.FieldLayout( this.hideEditor, {
		align: 'inline',
		label: ve.msg( 'enhancedupload-ve-attachmentinspector-grid-editor' )
	} );
	this.sizeLayout = new OO.ui.FieldLayout( this.showSize, {
		align: 'inline',
		label: ve.msg( 'enhancedupload-ve-attachmentinspector-grid-size' )
	} );
	this.categoriesLayout = new OO.ui.FieldLayout( this.showCategories, {
		align: 'inline',
		label: ve.msg( 'enhancedupload-ve-attachmentinspector-grid-categories' )
	} );
};

/**
 * @inheritdoc
 */
ve.ui.AttachmentInspector.prototype.getSetupProcess = function ( data ) {
	return ve.ui.AttachmentInspector.super.prototype.getSetupProcess.call( this, data )
		.next( function () {
			const attributes = this.selectedNode.getAttribute( 'mw' ).attrs;

			this.titleInput.setValue( attributes.title || '' );
			if ( attributes.categories ) {
				const categoryInput = attributes.categories.split( '|' );
				this.catInput.setValue( categoryInput );
			}

			this.descInput.setValue( attributes.description || '' );
			this.prefixInput.setValue( attributes.prefix || '' );
			if ( attributes.skipoptions ) {
				this.optionsInput.setSelected( attributes.skipoptions );
			}
			if ( attributes.hideversion ) {
				this.hideVersion.setSelected( attributes.hideversion );
			}
			if ( attributes.hideeditor ) {
				this.hideEditor.setSelected( attributes.hideeditor );
			}
			if ( attributes.showsize ) {
				this.showSize.setSelected( attributes.showsize );
			}
			if ( attributes.showcategories ) {
				this.showCategories.setSelected( attributes.showcategories );
			}

			this.actions.setAbilities( { done: true } );
		}, this );
};

ve.ui.AttachmentInspector.prototype.wireEvents = function () {
	this.titleInput.on( 'change', this.onChangeHandler );
	this.catInput.on( 'change', this.onChangeHandler );
	this.descInput.on( 'change', this.onChangeHandler );
	this.prefixInput.on( 'change', this.onChangeHandler );
	this.optionsInput.on( 'change', this.onChangeHandler );
	this.hideVersion.on( 'change', this.onChangeHandler );
	this.hideEditor.on( 'change', this.onChangeHandler );
	this.showSize.on( 'change', this.onChangeHandler );
	this.showCategories.on( 'change', this.onChangeHandler );
};

ve.ui.AttachmentInspector.prototype.updateMwData = function ( mwData ) {
	ve.ui.AttachmentInspector.super.prototype.updateMwData.call( this, mwData );

	if ( this.titleInput.getValue() !== '' ) {
		mwData.attrs.title = this.titleInput.getValue();
	} else {
		delete ( mwData.attrs.title );
	}
	if ( this.prefixInput.getValue() !== '' ) {
		mwData.attrs.prefix = this.prefixInput.getValue();
	} else {
		delete ( mwData.attrs.prefix );
	}
	if ( this.catInput.getSelectedCategories().length > 0 ) {
		let categories = '';
		const selectedCategories = this.catInput.getSelectedCategories();
		for ( let i = 0; i < selectedCategories.length; i++ ) {
			const catText = selectedCategories[ i ];
			const catTitle = mw.Title.newFromText( catText, 14 );
			categories += catTitle.getMainText();
			if ( i + 1 < selectedCategories.length ) {
				categories += '|';
			}
		}
		mwData.attrs.categories = categories;
	} else {
		delete ( mwData.attrs.categories );
	}
	if ( this.descInput.getValue() !== '' ) {
		mwData.attrs.description = this.descInput.getValue();
	} else {
		delete ( mwData.attrs.description );
	}
	if ( this.optionsInput.isSelected() ) {
		mwData.attrs.skipoptions = '1';
	} else {
		delete ( mwData.attrs.skipoptions );
	}
	if ( this.hideVersion.isSelected() ) {
		mwData.attrs.hideversion = '1';
	} else {
		delete ( mwData.attrs.hideversion );
	}
	if ( this.hideEditor.isSelected() ) {
		mwData.attrs.hideeditor = '1';
	} else {
		delete ( mwData.attrs.hideeditor );
	}
	if ( this.showSize.isSelected() ) {
		mwData.attrs.showsize = '1';
	} else {
		delete ( mwData.attrs.showsize );
	}
	if ( this.showCategories.isSelected() ) {
		mwData.attrs.showcategories = '1';
	} else {
		delete ( mwData.attrs.showcategories );
	}
};

/**
 * @inheritdoc
 */
ve.ui.AttachmentInspector.prototype.formatGeneratedContentsError = function ( $element ) {
	return $element.text().trim();
};

/**
 * Append the error to the current tab panel.
 */
ve.ui.AttachmentInspector.prototype.onTabPanelSet = function () {
	this.indexLayout.getCurrentTabPanel().$element.append( this.generatedContentsError.$element );
};

/* Registration */

ve.ui.windowFactory.register( ve.ui.AttachmentInspector );
