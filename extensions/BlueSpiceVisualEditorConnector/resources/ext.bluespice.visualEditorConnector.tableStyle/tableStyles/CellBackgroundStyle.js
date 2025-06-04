bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.CellBackgroundStyle = function () {
	bs.vec.ui.CellBackgroundStyle.super.apply( this );
	this.section = bs.vec.ui.TableStyle.static.SECTION_CELL;
	this.applyTo = bs.vec.ui.TableStyle.static.ELEMENT_CELL;
};

OO.inheritClass( bs.vec.ui.CellBackgroundStyle, bs.vec.ui.TableStyle );

bs.vec.ui.CellBackgroundStyle.prototype.getAttribute = function () {
	return 'background-color';
};

bs.vec.ui.CellBackgroundStyle.prototype.getUnit = function () {
	return bs.vec.ui.TableStyle.static.UNIT_NONE;
};

bs.vec.ui.CellBackgroundStyle.prototype.clearColor = function ( $element ) {
	const classes = $element[ 0 ].classList.values();
	for ( const cellClass of classes ) {
		if ( cellClass.match( /col-\S+/ ) ) {
			$element[ 0 ].classList.remove( cellClass ); // eslint-disable-line mediawiki/class-doc
		}
	}
	$element[ 0 ].style.backgroundColor = '';
	$element[ 0 ].removeAttribute( 'style' );
	return $element;
};

bs.vec.ui.CellBackgroundStyle.prototype.decorate = function ( $element ) {
	$element = this.clearColor( $element );
	if ( this.value.hasOwnProperty( 'code' ) ) {
		return $element.css( this.getAttribute(), this.value.code );
	} else if ( this.value.hasOwnProperty( 'class' ) ) {
		return $element.addClass( this.value.class ); // eslint-disable-line mediawiki/class-doc
	}
};

bs.vec.ui.CellBackgroundStyle.prototype.getTool = function () {
	return {
		widget: bs.vec.ui.widget.CellBackgroundWidget,
		displaySection: bs.vec.ui.TableStyle.static.TYPE_QUICK
	};
};

bs.vec.ui.CellBackgroundStyle.prototype.getModelProperty = function () {
	return 'cellBackgroundColor';
};

bs.vec.ui.CellBackgroundStyle.prototype.toDataElement = function ( section, domElement, result ) {
	let styleParser, classes;
	if ( !this.applies( section ) ) {
		return;
	}
	const style = domElement.getAttribute( 'style' );
	if ( style ) {
		styleParser = new bs.vec.util.StyleAttributeParser( style );
		if ( styleParser.getValueForAttr( this.getAttribute() ) ) {
			result.cellBackgroundColor = {
				code: styleParser.getValueForAttr( this.getAttribute() )
			};
		}
	} else {
		classes = domElement.getAttribute( 'class' );
		if ( !classes ) {
			return;
		}
		classes = classes.split( ' ' );
		if ( classes.length === 0 || classes.length > 1 ) {
			return;
		}

		result.cellBackgroundColor = {
			class: classes[ 0 ]
		};
	}
};

bs.vec.ui.CellBackgroundStyle.prototype.toDomElements = function ( section, dataElement, domElement, attributes ) { // eslint-disable-line no-unused-vars
	let style;

	if ( !this.applies( section ) ) {
		return;
	}
	// if no changes were done with background-color property in current cell
	if ( !dataElement.attributes.hasOwnProperty( 'cellBackgroundColor' ) ) {
		return;
	}

	const value = dataElement.attributes.cellBackgroundColor;
	style = domElement.getAttribute( 'style' );
	if ( !style ) {
		style = '';
	}
	const styleParser = new bs.vec.util.StyleAttributeParser( style );

	// clearing background style and colors from domElement
	// if bg-color was fully cleared from cell value will be an empty object
	styleParser.addToStyle( this.getAttribute(), '' );
	domElement.setAttribute( 'style', styleParser.toString() );
	domElement.setAttribute( 'class', '' );

	if ( $.isEmptyObject( value ) ) {
		return domElement;
	}
	if ( value.hasOwnProperty( 'code' ) ) {
		styleParser.addToStyle( this.getAttribute(), value.code );
		domElement.setAttribute( 'style', styleParser.toString() );
	} else if ( value.hasOwnProperty( 'class' ) ) {
		domElement.setAttribute( 'class', value.class );
	}
	return domElement;
};

bs.vec.registry.TableStyle.register( 'cellBackgroundColor', new bs.vec.ui.CellBackgroundStyle() );
