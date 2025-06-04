bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.ColorAnnotation = function ( element, store ) {
	// Parent constructor
	bs.vec.dm.ColorAnnotation.super.apply( this, [ element, store ] );

	if ( element && element.hasOwnProperty( 'attributes' ) ) {
		this.colorData = element.attributes;
	}
};

/* Inheritance */

OO.inheritClass( bs.vec.dm.ColorAnnotation, ve.dm.Annotation );

/* Static Properties */

bs.vec.dm.ColorAnnotation.static.name = 'textStyle/color';

bs.vec.dm.ColorAnnotation.static.matchTagNames = [ 'span' ];

bs.vec.dm.ColorAnnotation.static.matchFunction = function ( element ) {
	const textColors = bs.vec.config.get( 'ColorPickerColors' );
	const bgColors = bs.vec.config.get( 'ColorPickerColorsBackground' );
	const colorClasses = textColors.concat( bgColors );
	for ( let i = 0; i < colorClasses.length; i++ ) {
		const className = colorClasses[ i ].class;
		if ( element.classList.contains( className ) ) {
			return true;
		}
	}

	const colorCode = this.getCodeFromElement( element );
	if ( colorCode !== '' ) {
		return true;
	}
	return false;
};

bs.vec.dm.ColorAnnotation.static.applyToAppendedContent = true;

bs.vec.dm.ColorAnnotation.static.toDataElement = function ( domElements ) {
	return {
		type: this.name,
		attributes: {
			class: domElements[ 0 ].getAttribute( 'class' ) || '',
			code: this.getCodeFromElement( domElements[ 0 ] ),
			style: domElements[ 0 ].getAttribute( 'style' ) || ''
		}
	};
};

bs.vec.dm.ColorAnnotation.static.getCodeFromElement = function ( el ) {
	let prop, i, propBits, val, propName;
	const style = el.getAttribute( 'style' );
	if ( !style ) {
		return '';
	}
	const props = style.split( ';' );
	for ( i = 0; i < props.length; i++ ) {
		prop = props[ i ].trim();
		if ( prop === '' ) {
			continue;
		}
		propBits = prop.split( ':' );
		if ( propBits.length < 2 ) {
			continue;
		}
		val = propBits.pop().trim();
		propName = propBits.pop().trim();
		if ( propName === 'color' ) {
			return val;
		}
	}
	return '';
};

bs.vec.dm.ColorAnnotation.static.toDomElements = function ( dataElement, doc ) {
	const domElement = doc.createElement( 'span' );
	if ( dataElement.attributes.class ) {
		domElement.setAttribute( 'class', dataElement.attributes.class );
	}
	if ( dataElement.attributes.code ) {
		domElement.setAttribute( 'style', 'color: ' + dataElement.attributes.code );
	}
	if ( dataElement.attributes.style ) {
		domElement.setAttribute( 'style', dataElement.attributes.style );
	}

	return [ domElement ];
};

/**
 * @return {Object}
 */
bs.vec.dm.ColorAnnotation.prototype.getComparableObject = function () {
	return {
		type: this.name,
		class: this.getAttribute( 'class' ),
		style: this.getAttribute( 'style' )
	};
};

/**
 * Hash must be unique for each combination of values, otherwise,
 * annotations will be re-used instead of creating new ones
 *
 * @return {Object}
 */
bs.vec.dm.ColorAnnotation.prototype.getHashObject = function () {
	const attr = {};
	const hash = this.constructor.static.getHashObject( this.element );
	if ( this.colorData.hasOwnProperty( 'code' ) ) {
		attr.code = this.colorData.code;
	}
	if ( this.colorData.hasOwnProperty( 'class' ) ) {
		attr.code = this.colorData.class;
	}
	hash.attributes = attr;
	return hash;
};

/* Registration */

ve.dm.modelRegistry.register( bs.vec.dm.ColorAnnotation );
