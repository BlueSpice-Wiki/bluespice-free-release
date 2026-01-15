bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.MWFileLinkAnnotationWidget = function () {
	bs.vec.ui.MWFileLinkAnnotationWidget.super.apply( this, arguments );
	this.addOtherTools();
	this.input.$element.hide();
};

OO.inheritClass( bs.vec.ui.MWFileLinkAnnotationWidget, ve.ui.MWExternalLinkAnnotationWidget );

bs.vec.ui.MWFileLinkAnnotationWidget.prototype.onTextChange = function ( value ) {
	if ( this.isExternal ) {
		return bs.vec.ui.MWFileLinkAnnotationWidget.parent.prototype.onTextChange.call( this, value );
	}

	this.internalFilePicker.query.getValidity()
		.done( () => {
			this.setAnnotation( this.constructor.static.getAnnotationFromText( value, true ), true );
		} )
		.fail( () => {
			this.setAnnotation( null, true );
		} );
};

bs.vec.ui.MWFileLinkAnnotationWidget.prototype.addOtherTools = function () {
	this.internalFilePicker = new mw.widgets.TitleSearchWidget( {
		icon: 'search',
		showRedlink: true,
		namespace: bs.ns.NS_FILE,
		excludeCurrentPage: true,
		showImages: mw.config.get( 'wgVisualEditor' ).usePageImages,
		showDescriptions: mw.config.get( 'wgVisualEditor' ).usePageDescriptions,
		cache: ve.init.platform.linkCache
	} );
	this.internalFilePicker.$element.prepend( this.internalFilePicker.$query );
	this.internalFilePicker.query.connect( this, {
		change: 'onTextChange'
	} );

	this.$element.append( new OO.ui.FieldsetLayout( {
		items: [
			this.internalFilePicker
		]
	} ).$element );
};

bs.vec.ui.MWFileLinkAnnotationWidget.prototype.getInternalFilePicker = function () {
	return this.internalFilePicker;
};

bs.vec.ui.MWFileLinkAnnotationWidget.static.getAnnotationFromText = function ( value ) {
	value = value.trim();

	// Keep annotation in sync with value
	if ( value === '' ) {
		return null;
	} else {
		const title = mw.Title.makeTitle( bs.ns.NS_FILE, value );
		return bs.vec.dm.InternalFileLinkAnnotation.static.newFromTitle( title );
	}
};

bs.vec.ui.MWFileLinkAnnotationWidget.prototype.setAnnotation = function ( annotation, fromText ) {
	bs.vec.ui.MWFileLinkAnnotationWidget.parent.prototype.setAnnotation.call( this, annotation, fromText );
	if ( !this.annotation ) {
		return;
	}
	if ( !fromText ) {
		this.internalFilePicker.query.setValue( this.constructor.static.getTextFromAnnotation( this.annotation ) );
	}

	return this;
};

bs.vec.ui.MWFileLinkAnnotationWidget.static.getTextFromAnnotation = function ( annotation ) {
	if ( !annotation ) {
		return '';
	}
	const type = annotation.name;
	if ( type === 'link/internalFile' ) {
		return annotation.element.attributes.normalizedTitle;
	} else {
		return '';
	}
};
