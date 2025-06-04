'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.booklet = ext.pdfcreator.ui.booklet || {};
ext.pdfcreator.ui.booklet.pages = ext.pdfcreator.ui.booklet.pages || {};

require( './../../widgets/ParamsCombobox.js' );

ext.pdfcreator.ui.booklet.pages.SectionPage = function ( name, cfg ) {
	ext.pdfcreator.ui.booklet.pages.SectionPage.parent.call( this, name, cfg );
	this.data = cfg.data || {};

	this.params = cfg.params;
	this.preview = [];
	for ( const key in this.params ) {
		this.preview.push( {
			key: this.params[ key ].example
		} );
	}

	this.$overlay = cfg.$overlay;
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

OO.inheritClass( ext.pdfcreator.ui.booklet.pages.SectionPage, OO.ui.PageLayout );

ext.pdfcreator.ui.booklet.pages.SectionPage.prototype.getElements = function () {
	return [];
};

ext.pdfcreator.ui.booklet.pages.SectionPage.prototype.getData = function () {
	return {};
};

ext.pdfcreator.ui.booklet.pages.SectionPage.prototype.getParamsCombobox = function ( value ) {
	const options = [];
	for ( const paramKey in this.params ) {
		options.push( {
			data: '{{{' + this.params[ paramKey ].key + '}}}'
		} );
	}
	return new ext.pdfcreator.ui.ParamsCombobox( {
		padded: true,
		options: options,
		value: value || '',
		$overlay: this.$overlay
	} );
};
