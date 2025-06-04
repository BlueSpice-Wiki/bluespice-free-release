'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.booklet = ext.pdfcreator.ui.booklet || {};
ext.pdfcreator.ui.booklet.pages = ext.pdfcreator.ui.booklet.pages || {};

ext.pdfcreator.ui.booklet.pages.Upload = function ( name, cfg ) {
	ext.pdfcreator.ui.booklet.pages.Upload.parent.call( this, name, cfg );
	this.backToPage = '';
	this.layout = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	const formElements = this.getElements();
	this.layout.$element.append(
		formElements.map( ( item ) => item.$element )
	);

	this.$element.append( this.layout.$element );
};

OO.inheritClass( ext.pdfcreator.ui.booklet.pages.Upload, OO.ui.PageLayout );

ext.pdfcreator.ui.booklet.pages.Upload.prototype.getElements = function () {
	this.imageInput = new OO.ui.SelectFileInputWidget( {
		button: {
			label: mw.message( 'pdfcreator-template-edit-dlg-upload-select-btn-text-label' ).text()
		},
		showDropTarget: true
	} );

	this.imageInput.connect( this, {
		change: function ( files ) {
			this.emit( 'fileSet', files );
		}
	} );

	return [
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.imageInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-upload-label' ).text(),
					align: 'top'
				} )
			]
		} )
	];
};

ext.pdfcreator.ui.booklet.pages.Upload.prototype.getBackToPage = function () {
	this.imageInput.setValue( '' );
	return this.backToPage;
};

ext.pdfcreator.ui.booklet.pages.Upload.prototype.setBackTo = function ( backTo ) {
	this.backToPage = backTo;
};

ext.pdfcreator.ui.booklet.pages.Upload.prototype.getFile = function () {
	return this.imageInput.getValue();
};
