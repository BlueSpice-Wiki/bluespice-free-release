bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.IndentText = function () {
	// Parent constructor
	bs.vec.ce.IndentText.super.apply( this, arguments );

	// DOM changes
	this.$element
		.addClass( 've-ce-textStyleAnnotation' )
		.addClass( 've-ce-alignCenter' )
		.addClass( 've-ce-bidi-isolate' )
		.prop( {
			title: this.constructor.static.getDescription( this.model )
		} );

	const $newElement = $( '<dd>' ).append( $( '<dl>' ) ).appendTo( this.$element );
	this.$element = $newElement;
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.IndentText, ve.ce.Annotation );

/* Static Properties */

bs.vec.ce.IndentText.static.name = 'textStyle/indent-text';

bs.vec.ce.IndentText.static.tagName = 'p';

/* Static Methods */

/**
 * @inheritdoc
 */
bs.vec.ce.IndentText.static.getDescription = function ( model ) { // eslint-disable-line no-unused-vars
	return OO.ui.deferMsg( 'bs-visualeditorconnector-indent-annotation-desc' );
};

/* Registration */

ve.ce.annotationFactory.register( bs.vec.ce.IndentText );
