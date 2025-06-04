bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.CellBorderStyle = function () {
	this.matrix = {};
	bs.vec.ui.CellBorderStyle.super.apply( this );
	this.section = bs.vec.ui.TableStyle.static.SECTION_CELL;
	this.applyTo = bs.vec.ui.TableStyle.static.ELEMENT_CELL;

};

OO.inheritClass( bs.vec.ui.CellBorderStyle, bs.vec.ui.TableStyle );

bs.vec.ui.CellBorderStyle.prototype.getAttribute = function () {
	return null;
};

bs.vec.ui.CellBorderStyle.prototype.getUnit = function () {
	return bs.vec.ui.TableStyle.static.UNIT_NONE;
};

bs.vec.ui.CellBorderStyle.prototype.decorate = function ( $element ) {
	if ( !this.value ) {
		return;
	}

	let styleParser = new bs.vec.util.StyleAttributeParser( $element.attr( 'style' ) || '' );
	styleParser = this.translateToStyle( styleParser, this.value );

	return $element.attr( 'style', styleParser.toString() );
};

bs.vec.ui.CellBorderStyle.prototype.translateToStyle = function ( styleParser, value ) {
	this.foreachProp( ( complete, position, prop ) => {
		if ( value.hasOwnProperty( position ) ) {
			if ( !value[ position ] ) {
				styleParser.removeFromStyle( complete );
			} else if ( value[ position ].hasOwnProperty( prop ) ) {
				if ( value[ position ][ prop ] === null ) {
					styleParser.removeFromStyle( complete );
				} else {
					styleParser.addToStyle( complete, value[ position ][ prop ] );
				}
			}
		}
	} );

	return styleParser;
};

bs.vec.ui.CellBorderStyle.prototype.foreachProp = function ( cb, pos, prop ) {
	const props = [ 'width', 'color', 'style' ],
		positions = [ 'left', 'right', 'top', 'bottom' ];
	pos = pos || false;
	prop = prop || false;

	for ( let pr = 0; pr < props.length; pr++ ) {
		if ( prop && props[ pr ] !== prop ) {
			continue;
		}
		for ( let po = 0; po < positions.length; po++ ) {
			if ( pos && positions[ po ] !== pos ) {
				continue;
			}
			cb( 'border-' + positions[ po ] + '-' + props[ pr ], positions[ po ], props[ pr ] );
		}
	}
};

bs.vec.ui.CellBorderStyle.prototype.getTool = function () {
	return {
		widget: bs.vec.ui.widget.CellBorderWidget,
		displaySection: bs.vec.ui.TableStyle.static.TYPE_QUICK
	};
};

bs.vec.ui.CellBorderStyle.prototype.executeAction = function ( subject, args ) {
	// Point of the code below is to deep merge existing and new properties,
	// in order to now affect borders that we are not trying to currently set
	const fromMatrix = this.matrix[ args.row ] ? this.matrix[ args.row ][ args.col ] : null;
	const existing = fromMatrix || subject.node.element.cellBorder || {},
		merged = Object.assign( Object.assign( {}, existing ), args );

	for ( const prop in merged ) {
		if (
			existing.hasOwnProperty( prop ) && args.hasOwnProperty( prop ) &&
			typeof existing[ prop ] === 'object'
		) {
			if ( args[ prop ] === null ) {
				merged[ prop ] = null;
				continue;
			}
			if ( existing[ prop ] === null ) {
				merged[ prop ] = args[ prop ];
				continue;
			}
			if ( typeof args[ prop ] === 'object' ) {
				merged[ prop ] = Object.assign( Object.assign( {}, existing[ prop ] ), args[ prop ] );

				// This is a hacky solution for setting border style. We use border-style property
				// for hiding borders as well for setting actual styles. We must take care of this
				// when changing styles, so that only visible borders are changed, leaving invisibles with border-style: none
				if ( args[ prop ].hasOwnProperty( 'style' ) && args[ prop ].style === 'init' ) {
					// This happens on changing the visible borders of a cell, eg. from top-only to left-only
					// Any style set on that top border cannot be transferred to new border, so we init it to 'solid'
					merged[ prop ].style = 'solid';
				} else if (
					// If, however, we try to change style on already visible borders, do not touch borders that are invisible
					args[ prop ].hasOwnProperty( 'style' ) &&
					existing[ prop ].hasOwnProperty( 'style' ) &&
					existing[ prop ].style === 'none'
				) {
					merged[ prop ].style = 'none';
				}
			}
		}
	}

	if ( !this.matrix.hasOwnProperty( merged.row ) ) {
		this.matrix[ merged.row ] = {};
	}
	this.matrix[ merged.row ][ merged.col ] = merged;
	subject.node.element.attributes.cellBorder = merged;

	return subject;
};

bs.vec.ui.CellBorderStyle.prototype.getModelProperty = function () {
	return 'cellBorder';
};

bs.vec.ui.CellBorderStyle.prototype.toDataElement = function ( section, domElement, result ) {
	let propValue;
	const cellBorder = {};
	if ( !this.applies( section ) ) {
		return;
	}

	const style = domElement.getAttribute( 'style' );
	if ( !style ) {
		return;
	}
	const styleParser = new bs.vec.util.StyleAttributeParser( style );
	this.foreachProp( ( complete, position, prop ) => {
		propValue = styleParser.getValueForAttr( complete );
		if ( !propValue ) {
			return;
		}
		cellBorder[ position ] = cellBorder[ position ] || {};
		cellBorder[ position ][ prop ] = propValue;
	} );

	result[ this.getModelProperty() ] = cellBorder;
};

bs.vec.ui.CellBorderStyle.prototype.toDomElements = function ( section, dataElement, domElement, attributes ) { // eslint-disable-line no-unused-vars
	let styleParser;
	if ( section !== this.applyTo ) {
		return;
	}

	if ( !dataElement.attributes.hasOwnProperty( this.getModelProperty() ) ) {
		return domElement;
	}
	const style = domElement.getAttribute( 'style' ) || '';
	styleParser = new bs.vec.util.StyleAttributeParser( style );
	styleParser = this.translateToStyle( styleParser, dataElement.attributes[ this.getModelProperty() ] );

	domElement.setAttribute( 'style', styleParser.toString() );
};

bs.vec.ui.CellBorderStyle.prototype.shouldOverrideAction = function () {
	return false;
};

bs.vec.registry.TableStyle.register( 'cellBorder', new bs.vec.ui.CellBorderStyle() );
