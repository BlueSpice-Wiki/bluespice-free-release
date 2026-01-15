'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.booklet = ext.pdfcreator.ui.booklet || {};

require( './pages/SectionPage.js' );
require( './pages/Outro.js' );
require( './pages/Intro.js' );
require( './pages/Footer.js' );
require( './pages/General.js' );
require( './pages/Header.js' );
require( './pages/Selection.js' );
require( './pages/Upload.js' );

ext.pdfcreator.ui.booklet.EditBooklet = function ( cfg ) {
	ext.pdfcreator.ui.booklet.EditBooklet.parent.call( this, cfg );
	this.data = cfg.data || {};
	this.params = cfg.params || {};
	this.$overlay = cfg.$overlay;
	this.makePages();
};

OO.inheritClass( ext.pdfcreator.ui.booklet.EditBooklet, OO.ui.BookletLayout );

ext.pdfcreator.ui.booklet.EditBooklet.prototype.makePages = function () {
	this.pages = [
		new ext.pdfcreator.ui.booklet.pages.General( 'General', {
			expanded: false,
			data: this.data.general,
			params: this.params
		} ),
		new ext.pdfcreator.ui.booklet.pages.Selection( 'Selection', {
			expanded: false,
			data: this.data,
			params: this.params
		} ),
		new ext.pdfcreator.ui.booklet.pages.Header( 'Header', {
			expanded: false,
			data: this.data.header,
			$overlay: this.$overlay,
			params: this.params
		} ),
		new ext.pdfcreator.ui.booklet.pages.Footer( 'Footer', {
			expanded: false,
			data: this.data.footer,
			params: this.params
		} ),
		new ext.pdfcreator.ui.booklet.pages.Intro( 'Intro', {
			expanded: false,
			data: this.data.intro,
			params: this.params
		} ),
		new ext.pdfcreator.ui.booklet.pages.Outro( 'Outro', {
			expanded: false,
			data: this.data.outro,
			params: this.params
		} ),
		new ext.pdfcreator.ui.booklet.pages.Upload( 'Upload', {
			expanded: false
		} )
	];

	this.pagesOrder = [
		'General',
		'Selection',
		'Header',
		'Footer',
		'Intro',
		'Outro',
		'Upload'
	];

	this.addPages( this.pages );
};
