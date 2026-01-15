bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.TextAlignmentCenter = function () {
	// Parent constructor
	bs.vec.ce.TextAlignmentCenter.super.apply( this, arguments );

	// DOM changes
	this.$element
		.addClass( 've-ce-textStyleAnnotation' )
		.addClass( 've-ce-alignCenter' )
		.addClass( 've-ce-bidi-isolate' )
		.prop( {
			title: this.constructor.static.getDescription( this.model )
		} );

	this.$element.css( 'text-align', 'center' );
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.TextAlignmentCenter, ve.ce.Annotation );

/* Static Properties */

bs.vec.ce.TextAlignmentCenter.static.name = 'textStyle/align-center';

bs.vec.ce.TextAlignmentCenter.static.tagName = 'p';

/* Static Methods */

/**
 * @inheritdoc
 */
bs.vec.ce.TextAlignmentCenter.static.getDescription = function ( model ) { // eslint-disable-line no-unused-vars
	return OO.ui.deferMsg( 'bs-visualeditorconnector-align-center-annotation-desc' );
};

/* Registration */

ve.ce.annotationFactory.register( bs.vec.ce.TextAlignmentCenter );
