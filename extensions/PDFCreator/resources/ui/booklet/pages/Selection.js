'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.booklet = ext.pdfcreator.ui.booklet || {};
ext.pdfcreator.ui.booklet.pages = ext.pdfcreator.ui.booklet.pages || {};

require( './../../widgets/SelectTemplateSectionWidget.js' );

ext.pdfcreator.ui.booklet.pages.Selection = function ( name, cfg ) {
	this.templateSections = [];
	this.data = cfg.data;
	this.headerSelected = Object.keys( this.data.header ).length > 0;
	this.footerSelected = Object.keys( this.data.footer ).length > 0;
	this.introSelected = Object.keys( this.data.intro ).length > 0;
	this.outroSelected = Object.keys( this.data.outro ).length > 0;
	ext.pdfcreator.ui.booklet.pages.Selection.parent.call( this, name, cfg );
};

OO.inheritClass( ext.pdfcreator.ui.booklet.pages.Selection,
	ext.pdfcreator.ui.booklet.pages.SectionPage );

ext.pdfcreator.ui.booklet.pages.Selection.prototype.getElements = function () {
	this.headerSelection = new ext.pdfcreator.ui.SelectTemplateSectionWidget( {
		section: 'header',
		label: mw.message( 'pdfcreator-template-edit-dlg-header-label' ).text(),
		selected: this.headerSelected || false
	} );
	this.headerSelection.connect( this, {
		change: function ( selected ) {
			this.headerSelected = selected;
		}
	} );
	this.templateSections.header = this.headerSelection;

	this.footerSelection = new ext.pdfcreator.ui.SelectTemplateSectionWidget( {
		section: 'footer',
		label: mw.message( 'pdfcreator-template-edit-dlg-footer-label' ).text(),
		selected: this.footerSelected || false
	} );
	this.footerSelection.connect( this, {
		change: function ( selected ) {
			this.footerSelected = selected;
		}
	} );
	this.templateSections.footer = this.footerSelection;

	this.introSelection = new ext.pdfcreator.ui.SelectTemplateSectionWidget( {
		section: 'intro',
		label: mw.message( 'pdfcreator-template-edit-dlg-intro-label' ).text(),
		selected: this.introSelected || false
	} );
	this.introSelection.connect( this, {
		change: function ( selected ) {
			this.introSelected = selected;
		}
	} );
	this.templateSections.intro = this.introSelection;

	this.outroSelection = new ext.pdfcreator.ui.SelectTemplateSectionWidget( {
		section: 'outro',
		label: mw.message( 'pdfcreator-template-edit-dlg-outro-label' ).text(),
		selected: this.outroSelected || false
	} );
	this.outroSelection.connect( this, {
		change: function ( selected ) {
			this.outroSelected = selected;
		}
	} );
	this.templateSections.outro = this.outroSelection;

	for ( const section in this.templateSections ) {
		this.templateSections[ section ].connect( this, {
			configureSection: function ( sectionKey ) {
				this.emit( 'configureSection', sectionKey );
			}
		} );
	}

	return [
		new OO.ui.LabelWidget( {
			label: mw.message( 'pdfcreator-template-edit-dlg-selection-page-desc' ).text(),
			classes: [ 'pdfcreator-edit-desc' ]
		} ),
		this.headerSelection,
		this.footerSelection,
		this.introSelection,
		this.outroSelection
	];
};

ext.pdfcreator.ui.booklet.pages.Selection.prototype.setSelected = function ( section, selected ) {
	this.templateSections[ section ].setSelected( selected );
};

ext.pdfcreator.ui.booklet.pages.Selection.prototype.getData = function () {
	const selectedSections = {};
	for ( const section in this.templateSections ) {
		if ( this.templateSections[ section ].isSelected() ) {
			selectedSections[ section ] = true;
		}
	}

	return selectedSections;
};
