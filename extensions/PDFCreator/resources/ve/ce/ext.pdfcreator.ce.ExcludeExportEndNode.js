'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.ce = ext.pdfcreator.ve.ce || {};

ext.pdfcreator.ve.ce.ExcludeExportEndNode = function () {
	// Parent constructor
	ext.pdfcreator.ve.ce.ExcludeExportEndNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ext.pdfcreator.ve.ce.ExcludeExportEndNode, ve.ce.MWExtensionNode );

/* Static properties */

ext.pdfcreator.ve.ce.ExcludeExportEndNode.static.name = 'pdfexcludeend';

ext.pdfcreator.ve.ce.ExcludeExportEndNode.static.primaryCommandName = 'excludeExportCommand';

// If body is empty, tag does not render anything
ext.pdfcreator.ve.ce.ExcludeExportEndNode.static.rendersEmpty = true;

ext.pdfcreator.ve.ce.ExcludeExportEndNode.prototype.generateContents = function ( config ) {
	const deferred = ve.createDeferred(),
		mwData = ve.copy( this.getModel().getAttribute( 'mw' ) ),
		extsrc = config && config.extsrc !== undefined ? config.extsrc : ( ve.getProp( mwData, 'body', 'extsrc' ) || '' ),
		tagName = this.getModel().getExtensionName();

	// XML-like tags in wikitext are not actually XML and don't expect their contents to be escaped.
	const wikitext = mw.html.element( tagName, [], new mw.html.Raw( extsrc ) );

	const xhr = ve.init.target.parseWikitextFragment(
		wikitext,
		false,
		this.getModel().getDocument() )
		.done( this.onParseSuccess.bind( this, deferred ) )
		.fail( this.onParseError.bind( this, deferred ) );
	return deferred.promise( { abort: xhr.abort } );
};

/* Registration */

ve.ce.nodeFactory.register( ext.pdfcreator.ve.ce.ExcludeExportEndNode );
