ext.visualEditorPlus.ui.InlineTextInspector = function ( config ) {
	config = config || {};
	config.padded = false;
	config.$overlay = true;
	config.position = 'below';
	config.autoFlip = false;
	config.autoClose = false;
	config.width = null;
	ext.visualEditorPlus.ui.InlineTextInspector.super.call( this, config );
	this.inspectors = [];

	this.$element.addClass( 'ext-visualEditorPlus-inlineTextInspector' );
	this.$anchor.remove();

	this.oldWidth = 0;

	this.target = null;
};

OO.inheritClass( ext.visualEditorPlus.ui.InlineTextInspector, OO.ui.PopupWidget );

ext.visualEditorPlus.ui.InlineTextInspector.prototype.setTarget = function ( target ) {
	this.target = target;
	this.init();
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.addInspector = function ( name, inspector ) {
	this.inspectors.push( inspector );
	inspector.init();
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.appendInspectors = function () {
	let header, i;
	this.inspectors.sort( ( a, b ) => a.getPriority() - b.getPriority() );
	for ( i = 0; i < this.inspectors.length; i++ ) {
		header = this.inspectors[ i ].getHeader();
		if ( header ) {
			this.inspectors[ i ].$element.prepend( header.$element );
		}
		this.$body.append( this.inspectors[ i ].$element );
	}
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.init = function () {
	this.surface = this.target.getSurface();
	this.surfaceModel = this.surface.getModel();
	this.selection = null;
	this.selectedNode = null;

	this.surfaceModel.on( 'select', this.onSelection.bind( this ) );
	this.target.getToolbar().on( 'updateState', this.onToolbarUpdateState.bind( this ) );
	$( document ).on( 'mousedown', this.onMouseDown.bind( this ) );
	$( document ).on( 'mouseup', this.onMouseUp.bind( this ) );
	$( window ).on( 'resize', this.onResize.bind( this ) );
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.onSelection = function ( selection ) {
	if ( !( selection instanceof ve.dm.LinearSelection ) ) {
		this.selection = null;
		this.selectedNode = null;
		return;
	}
	this.selection = selection;
	this.selectedNode = this.surfaceModel.getSelectedNode();
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.onToolbarUpdateState = function () {
	this.toggle( false );
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.onMouseDown = function ( e ) {
	if ( e.which !== 1 ) {
		return;
	}
	if ( this.shouldHide( e ) ) {
		this.toggle( false );
	}
	if ( !this.isVisible() ) {
		this.location = {
			start: {
				x: e.pageX,
				y: e.pageY
			}
		};
	}
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.onMouseUp = function ( e ) {
	if ( !this.location || this.isVisible() ) {
		return;
	}
	this.location.end = {
		x: e.pageX,
		y: e.pageY
	};
	if ( !this.selection || ( this.selectedNode && !( this.selectedNode instanceof ve.dm.TextNode ) ) ) {
		// If some particular node is selected, we don't want to show the inspector, just on plain text
		return;
	}
	const range = this.selection.getRange();
	const selectedText = window.getSelection().toString();
	const strLength = selectedText.trim().length;
	if ( strLength ) {
		this.inspect( range, selectedText );
	}
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.onResize = function () {
	this.toggle( false );
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.shouldHide = function ( e ) {
	const $target = $( e.target );
	// eslint-disable-next-line no-jquery/no-class-state
	if ( $target.hasClass( 'ext-visualEditorPlus-inlineTextInspector' ) ) {
		return false;
	}
	if ( $target.parents( '.ext-visualEditorPlus-inlineTextInspector' ).length !== 0 ) {
		return false;
	}

	return true;
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.inspect = function ( range, selectedText ) {
	let i;
	this.toggle( true );
	for ( i = 0; i < this.inspectors.length; i++ ) {
		this.inspectors[ i ].inspect( range, selectedText );
	}
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.getDesiredPosition = function () {
	const xStart = this.location.start.x;
	const xEnd = this.location.end.x;
	const yStart = this.location.start.y;
	const yEnd = this.location.end.y;
	const xBlockStart = xStart < xEnd ? xStart : xEnd;
	const yBlockStart = yStart < yEnd ? yStart : yEnd;
	const xBlockEnd = xStart > xEnd ? xStart : xEnd;
	const yBlockEnd = yStart > yEnd ? yStart : yEnd;

	return {
		above: { x: xBlockStart, y: yBlockStart },
		below: { x: xBlockEnd, y: yBlockEnd }
	};
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.getEditorBoundary = function () {
	// eslint-disable-next-line no-jquery/no-global-selector, no-jquery/variable-pattern
	const target = $( '.ve-ui-surface' ),
		offset = target.offset(),
		width = target.width(),
		height = target.height();

	return {
		left: offset.left,
		right: offset.left + width,
		top: offset.top,
		bottom: offset.top + height
	};
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.computePosition = function () {
	// var x = ext.visualEditorPlus.ui.InlineTextInspector.super.prototype.computePosition.call( this );
	let desiredPosition;
	if ( !this.location || !this.location.hasOwnProperty( 'start' ) || !this.location.hasOwnProperty( 'end' ) ) {
		return ext.visualEditorPlus.ui.InlineTextInspector.super.prototype.computePosition.call( this );
	}

	const boundary = this.getEditorBoundary();
	desiredPosition = this.getDesiredPosition();

	desiredPosition = desiredPosition[ this.getPosition() ];
	if ( desiredPosition.x < boundary.left + 20 ) {
		desiredPosition.x = boundary.left + 20;
	}
	const width = this.width || 300;
	if ( desiredPosition.x > boundary.right - width ) {
		desiredPosition.x = boundary.right - width;
	}
	if ( desiredPosition.y < boundary.top ) {
		desiredPosition.y = boundary.top;
	}
	if ( desiredPosition.y > boundary.bottom ) {
		desiredPosition.y = boundary.bottom - 100;
	}

	this.$container.css( {
		position: 'absolute',
		top: desiredPosition.y + 10,
		left: desiredPosition.x - 20
	} );

	const pos = ext.visualEditorPlus.ui.InlineTextInspector.super.prototype.computePosition.call( this );
	pos.left -= 20;
	return pos;
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.toggle = function ( show ) {
	let i;
	ext.visualEditorPlus.ui.InlineTextInspector.parent.prototype.toggle.call( this, show );

	if ( !show ) {
		for ( i = 0; i < this.inspectors.length; i++ ) {
			this.inspectors[ i ].onClose();
		}
		if ( this.selection ) {
			this.selection.collapseToEnd();
		}
	}
	if ( this.oldWidth ) {
		this.width = this.oldWidth;
	}
};

ext.visualEditorPlus.ui.InlineTextInspector.prototype.adaptPositionToSize = function ( width ) {
	if ( this.width !== width ) {
		this.oldWidth = this.width;
	}
	this.width = width;
	this.computePosition();
};
