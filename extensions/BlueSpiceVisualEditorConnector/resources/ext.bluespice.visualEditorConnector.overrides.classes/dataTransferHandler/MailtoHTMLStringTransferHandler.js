bs.vec.ui.MailtoHTMLStringTransferHandler = function () {
	// Parent constructor
	bs.vec.ui.MailtoHTMLStringTransferHandler.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.vec.ui.MailtoHTMLStringTransferHandler, ve.ui.HTMLStringTransferHandler );

/* Static properties */
bs.vec.ui.MailtoHTMLStringTransferHandler.static.name = 'mailtoProtocol';

bs.vec.ui.MailtoHTMLStringTransferHandler.static.types = [ 'text/plain', 'text/html', 'application/xhtml+xml' ];

bs.vec.ui.MailtoHTMLStringTransferHandler.static.handlesPaste = true;

/**
 * RegExp matching input containing "file:/" or "File:/".
 *
 * @property {RegExp}
 * @private
 */
bs.vec.ui.MailtoHTMLStringTransferHandler.static.urlRegExp = /<a.*href="(mailto:.*)".*>(.*)<\/a>$/i; // Initialized below

/**
 * Checks input item with urlRegExp expression
 *
 * @param {ve.ui.DataTransferItem} item
 * @return {boolean|undefined} Whether item matches against urlRegExp
 */
bs.vec.ui.MailtoHTMLStringTransferHandler.static.matchFunction = function ( item ) {
	if ( bs.vec.ui.MailtoHTMLStringTransferHandler.static.types.indexOf( item.type ) >= 0 ) {
		return bs.vec.ui.MailtoHTMLStringTransferHandler.static.urlRegExp.test(
			item.getAsString()
		);
	}
};

/**
 * @inheritdoc
 */
bs.vec.ui.MailtoHTMLStringTransferHandler.prototype.process = function () {
	const text = this.item.getAsString().trim(),
		matches = text.match( /<a.*href="(mailto:.*)".*>(.*)<\/a>/ );
	const url = matches[ 1 ],
		title = matches[ 2 ];

	const newLinkAnnotation = new ve.dm.MWExternalLinkAnnotation( {
		type: 'link/mwExternal',
		attributes: { href: url }
	} );

	const store = this.surface.getModel().getDocument().getStore();
	const annotationSet = new ve.dm.AnnotationSet( store, store.hashAll( [
		newLinkAnnotation
	] ) );
	const result = [ title ];
	ve.dm.Document.static.addAnnotationsToData( result, annotationSet );
	this.resolve( result );
};

/* Registration */
ve.ui.dataTransferHandlerFactory.register( bs.vec.ui.MailtoHTMLStringTransferHandler );
