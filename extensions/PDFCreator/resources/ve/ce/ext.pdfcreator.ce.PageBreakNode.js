'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ve = ext.pdfcreator.ve || {};
ext.pdfcreator.ve.ce = ext.pdfcreator.ve.ce || {};

ext.pdfcreator.ve.ce.PageBreakNode = function () {
	// Parent constructor
	ext.pdfcreator.ve.ce.PageBreakNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ext.pdfcreator.ve.ce.PageBreakNode, ve.ce.MWExtensionNode );

/* Static properties */

ext.pdfcreator.ve.ce.PageBreakNode.static.name = 'pdfpagebreak';

ext.pdfcreator.ve.ce.PageBreakNode.static.primaryCommandName = 'pagebreakCommand';

// If body is empty, tag does not render anything
ext.pdfcreator.ve.ce.PageBreakNode.static.rendersEmpty = false;

ext.pdfcreator.ve.ce.PageBreakNode.prototype.generateContents = function ( config ) {
	const deferred = ve.createDeferred(),
		mwData = ve.copy( this.getModel().getAttribute( 'mw' ) ),
		extsrc = config && config.extsrc !== undefined ? config.extsrc : ( ve.getProp( mwData, 'body', 'extsrc' ) || '' ),
		tagName = this.getModel().getExtensionName();

	// XML-like tags in wikitext are not actually XML and don't expect their contents to be escaped.
	const wikitext = mw.html.element( tagName, [], new mw.html.Raw( extsrc ) );

	const xhr = ve.init.target.parseWikitextFragment(
		wikitext,
		false,
		this.getModel().getDocument()
	).done( this.onParseSuccess.bind( this, deferred )
	).fail( this.onParseError.bind( this, deferred ) );
	return deferred.promise( { abort: xhr.abort } );
};

/* Registration */

ve.ce.nodeFactory.register( ext.pdfcreator.ve.ce.PageBreakNode );
