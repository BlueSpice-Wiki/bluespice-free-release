'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.ui = ext.pdfcreator.ve.ui || {};

ext.pdfcreator.ve.ui.ExportPDFInspector = function ( config ) {
	// Parent constructor
	ext.pdfcreator.ve.ui.ExportPDFInspector.super.call(
		this, ve.extendObject( { padded: true }, config )
	);
};

/* Inheritance */

OO.inheritClass( ext.pdfcreator.ve.ui.ExportPDFInspector, ve.ui.MWLiveExtensionInspector );

/* Static properties */

ext.pdfcreator.ve.ui.ExportPDFInspector.static.name = 'exportpdfInspector';

ext.pdfcreator.ve.ui.ExportPDFInspector.static.title = mw.message( 'pdfcreator-export-pdf-inspector-title' ).text();

ext.pdfcreator.ve.ui.ExportPDFInspector.static.modelClasses =
	[ ext.pdfcreator.ve.dm.ExportPDFNode ];

ext.pdfcreator.ve.ui.ExportPDFInspector.static.dir = 'ltr';

// This tag does not have any content
ext.pdfcreator.ve.ui.ExportPDFInspector.static.allowedEmpty = true;
ext.pdfcreator.ve.ui.ExportPDFInspector.static.selfCloseEmptyBody = false;

/**
 * @inheritdoc
 */
ext.pdfcreator.ve.ui.ExportPDFInspector.prototype.initialize = function () {
	ext.pdfcreator.ve.ui.ExportPDFInspector.super.prototype.initialize.call( this );

	// remove input field with links in it
	this.input.$element.remove();

	this.indexLayout = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true
	} );

	this.createFields();

	this.setLayouts();

	// Initialization
	this.$content.addClass( 'pdfcreator-exportpdf-inspector-content' );

	this.indexLayout.$element.append(
		this.pageTitleLayout.$element,
		this.modeLayout.$element,
		this.templateLayout.$element,
		this.labelLayout.$element
	);
	this.form.$element.append(
		this.indexLayout.$element
	);
};

ext.pdfcreator.ve.ui.ExportPDFInspector.prototype.createFields = function () {
	this.pageTitle = new OOJSPlus.ui.widget.TitleInputWidget( {
		$overlay: true
	} );
	this.mode = new ext.pdfcreator.ui.ModeSelector( {
		$overlay: true
	} );
	this.template = new ext.pdfcreator.ui.TemplateSelector( {
		$overlay: true
	} );
	this.label = new OO.ui.TextInputWidget();
};

ext.pdfcreator.ve.ui.ExportPDFInspector.prototype.setLayouts = function () {
	this.pageTitleLayout = new OO.ui.FieldLayout( this.pageTitle, {
		align: 'right',
		label: mw.message( 'pdfcreator-export-pdf-inspector-page-layout-label' ).text()
	} );
	this.modeLayout = new OO.ui.FieldLayout( this.mode, {
		align: 'right',
		label: mw.message( 'pdfcreator-export-pdf-inspector-export-mode-label' ).text()
	} );

	this.templateLayout = new OO.ui.FieldLayout( this.template, {
		align: 'right',
		label: mw.message( 'pdfcreator-export-pdf-inspector-export-template-label' ).text()
	} );

	this.labelLayout = new OO.ui.FieldLayout( this.label, {
		align: 'right',
		label: mw.message( 'pdfcreator-export-pdf-inspector-export-link-label' ).text()
	} );
};

/**
 * @inheritdoc
 */
ext.pdfcreator.ve.ui.ExportPDFInspector.prototype.getSetupProcess = function ( data ) {
	return ext.pdfcreator.ve.ui.ExportPDFInspector.super.prototype.getSetupProcess.call(
		this, data
	).next( function () {
		const attributes = this.selectedNode.getAttribute( 'mw' ).attrs;
		if ( attributes.page ) {
			this.pageTitle.setValue( attributes.page );
		}

		if ( attributes.mode ) {
			this.mode.selectByData( attributes.mode );
		}

		if ( attributes.template ) {
			this.template.selectByData( attributes.template );
		}

		if ( attributes.label ) {
			this.label.setValue( attributes.label );
		}
		this.actions.setAbilities( { done: true } );

		// Add event handlers
		this.pageTitle.on( 'change', this.onChangeHandler );
		this.mode.on( 'change', this.onChangeHandler );
		this.template.on( 'change', this.onChangeHandler );
		this.label.on( 'change', this.onChangeHandler );
	}, this );
};

ext.pdfcreator.ve.ui.ExportPDFInspector.prototype.updateMwData = function ( mwData ) {
	ext.pdfcreator.ve.ui.ExportPDFInspector.super.prototype.updateMwData.call( this, mwData );

	if ( this.pageTitle.getMWTitle() ) {
		mwData.attrs.page = this.pageTitle.getMWTitle().getPrefixedDb();
	} else {
		delete ( mwData.attrs.page );
	}

	if ( this.mode.getSelectedData() !== '' ) {
		mwData.attrs.mode = this.mode.getSelectedData();
	} else {
		delete ( mwData.attrs.mode );
	}
	if ( this.template.getSelectedData() !== '' ) {
		mwData.attrs.template = this.template.getSelectedData();
	} else {
		delete ( mwData.attrs.template );
	}
	if ( this.label.getValue() !== '' ) {
		mwData.attrs.label = this.label.getValue();
	} else {
		delete ( mwData.attrs.label );
	}
};

/**
 * @inheritdoc
 */
ext.pdfcreator.ve.ui.ExportPDFInspector.prototype.formatGeneratedContentsError =
	function ( $element ) {
		return $element.text().trim();
	};

/**
 * Append the error to the current tab panel.
 */
ext.pdfcreator.ve.ui.ExportPDFInspector.prototype.onTabPanelSet = function () {
	this.indexLayout.getCurrentTabPanel().$element.append( this.generatedContentsError.$element );
};

/* Registration */

ve.ui.windowFactory.register( ext.pdfcreator.ve.ui.ExportPDFInspector );
