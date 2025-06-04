'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.booklet = ext.pdfcreator.ui.booklet || {};
ext.pdfcreator.ui.booklet.pages = ext.pdfcreator.ui.booklet.pages || {};

ext.pdfcreator.ui.booklet.pages.Outro = function ( name, cfg ) {
	ext.pdfcreator.ui.booklet.pages.Outro.parent.call( this, name, cfg );
};

OO.inheritClass( ext.pdfcreator.ui.booklet.pages.Outro,
	ext.pdfcreator.ui.booklet.pages.SectionPage );

ext.pdfcreator.ui.booklet.pages.Outro.prototype.getElements = function () {
	this.descInput = new OO.ui.MultilineTextInputWidget( {
		padded: true,
		value: this.data.desc || ''
	} );
	return [
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.LabelWidget( {
					label: mw.message( 'pdfcreator-template-edit-dlg-outro-page-heading' ).text(),
					classes: [ 'pdfcreator-edit-heading' ]
				} ),
				new OO.ui.LabelWidget( {
					label: mw.message( 'pdfcreator-template-edit-dlg-outro-page-desc' ).text(),
					classes: [ 'pdfcreator-edit-desc' ]
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.descInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-outro-input-text-label' ).text(),
					align: 'top'
				} )
			]
		} )
	];
};

ext.pdfcreator.ui.booklet.pages.Outro.prototype.getData = function () {
	return {
		desc: this.descInput.getValue()
	};
};
