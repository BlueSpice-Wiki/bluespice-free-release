bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.TableAction = function () {
	// Parent constructor
	ve.ui.TableAction.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.vec.ui.TableAction, ve.ui.TableAction );

/* Static Properties */

bs.vec.ui.TableAction.static.name = 'bs-vec-table';

bs.vec.ui.TableAction.static.methods = [
	'duplicate', 'cellBackgroundColor', 'cellBorder', 'columnWidth',
	'horizontalTextAlignment', 'rowHeight', 'verticalTextAlignment'
];

bs.vec.ui.TableAction.prototype.cellBackgroundColor = function ( args ) {
	this.setAttributes( 'cellBackgroundColor', args.value );
};

bs.vec.ui.TableAction.prototype.columnWidth = function ( args ) {
	// We need to apply to every cell in the column
	const origSelection = this.selectSection( 'column' );
	this.setAttributes( 'columnWidth', args.columnWidth );
	this.restoreOriginalSelection( origSelection );
};

bs.vec.ui.TableAction.prototype.rowHeight = function ( args ) {
	// This is a different way to apply attributes to nodes
	// Since this is a RowNode (not a CellNode), we need to use a different approach
	const surfaceModel = this.surface.getModel(),
		selection = surfaceModel.getSelection(),
		node = selection.getTableNode( surfaceModel.getDocument() ),
		startRow = selection.startRow,
		endRow = selection.endRow;

	for ( let i = startRow; i <= endRow; i++ ) {
		const row = node.matrix.getRowNode( i );
		if ( !row ) {
			continue;
		}
		row.element.attributes = row.element.attributes || {};
		row.element.attributes.rowHeight = args.rowHeight;
	}
	// Just here to update CEs, model already updated at this point
	this.setAttributes( 'rowHeight', args.rowHeight );
};

bs.vec.ui.TableAction.prototype.verticalTextAlignment = function ( args ) {
	this.setAttributes( 'verticalTextAlignment', args.verticalTextAlignment );
};

bs.vec.ui.TableAction.prototype.horizontalTextAlignment = function ( args ) {
	this.setAttributes( 'horizontalTextAlignment', args.horizontalTextAlignment );
};

bs.vec.ui.TableAction.prototype.selectSection = function ( type, start, end ) {
	const surfaceModel = this.surface.getModel(),
		selection = surfaceModel.getSelection(),
		node = selection.getTableNode( surfaceModel.getDocument() ),
		matrix = node.getMatrix(),
		orig = {
			startRow: selection.startRow,
			endRow: selection.endRow,
			startCol: selection.startCol,
			endCol: selection.endCol
		};

	if ( type === 'row' ) {
		if ( start ) {
			selection.startRow = start;
		}
		if ( end ) {
			selection.endRow = end;
		}
		// Select whole row
		selection.startCol = 0;
		selection.endCol = matrix.matrix[ 0 ].length - 1;
	}
	if ( type === 'column' ) {
		if ( start ) {
			selection.startCol = start;
		}
		if ( end ) {
			selection.endCol = end;
		}
		// Select whole column
		selection.startRow = 0;
		selection.endRow = matrix.matrix.length - 1;
	}
	return orig;
};

bs.vec.ui.TableAction.prototype.restoreOriginalSelection = function ( data ) {
	const surfaceModel = this.surface.getModel(),
		selection = surfaceModel.getSelection();

	selection.startRow = data.startRow;
	selection.endRow = data.endRow;
	selection.startCol = data.startCol;
	selection.endCol = data.endCol;
};

bs.vec.ui.TableAction.prototype.setAttributes = function ( key, value ) {
	const surfaceModel = this.surface.getModel(),
		selection = surfaceModel.getSelection();

	if ( !( selection instanceof ve.dm.TableSelection ) ) {
		return false;
	}

	const attributes = {};
	attributes[ key ] = value;
	const txBuilders = [];
	const documentModel = surfaceModel.getDocument();
	const ranges = selection.getOuterRanges( documentModel );

	for ( let i = ranges.length - 1; i >= 0; i-- ) {
		txBuilders.push(
			ve.dm.TransactionBuilder.static.newFromAttributeChanges.bind( null,
				documentModel, ranges[ i ].start, attributes
			)
		);
	}
	txBuilders.forEach( ( txBuilder ) => {
		surfaceModel.change( txBuilder() );
	} );
	ve.track( 'activity.table', { action: key } );

	// this.restoreSelection( surfaceModel, selection, tableNode, oldSelection );
	return true;
};

bs.vec.ui.TableAction.prototype.hasMultiRowCells = function ( cells ) {
	let cellIndex, cell;

	for ( cellIndex = 0; cellIndex < cells.length; cellIndex++ ) {
		cell = cells[ cellIndex ];
		if ( !cell.node.element.attributes.hasOwnProperty( 'rowspan' ) ) {
			continue;
		}
		if ( cell.node.element.attributes.rowspan < 2 ) {
			continue;
		}
		return true;
	}
	return false;
};

bs.vec.ui.TableAction.prototype.duplicate = function ( mode ) {
	const surfaceModel = this.surface.getModel();
	const selection = surfaceModel.getSelection();
	const tableNode = selection.getTableNode( surfaceModel.getDocument() );
	const matrix = tableNode.getMatrix();
	const oldSelection = [ selection.fromCol, selection.fromRow, selection.toCol, selection.toRow ];
	let newRow, cells, itemIndex, indexAfterAdditions;

	if ( !( selection instanceof ve.dm.TableSelection ) ) {
		return false;
	}
	if ( mode === 'row' ) {
		indexAfterAdditions = selection.endRow;
		for ( itemIndex = selection.startRow; itemIndex <= indexAfterAdditions; itemIndex++ ) {
			cells = matrix.getRow( itemIndex );
			// If row contains cells that span over multiple rows, we cannot duplicate - alert user
			if ( this.hasMultiRowCells( cells ) ) {
				OO.ui.alert( OO.ui.deferMsg( 'bs-vec-row-duplicate-has-merged' ) );
				return;
			}
			newRow = {
				row: ve.dm.TableRowNode.static.applyStylings( matrix.getRowNode( itemIndex ), {} ),
				cells: cells.map( ( ce ) => {
					// This will set `cell.data` to "data", eg. `{tableCell}data{/tableCell}`. This is expected by
					// "insertRowOrCol" method.
					if ( ce && !ce.isPlaceholder() ) {
						ce.data = surfaceModel.getDocument().getData( ce.node.getOuterRange(), true );
					}
					return ce;
				} )
			};
			this.insertRowOrCol( tableNode, 'row', itemIndex, 'after', null, newRow );
			// Number of rows changed
			itemIndex++;
			indexAfterAdditions++;
		}
	} else if ( mode === 'col' ) {
		indexAfterAdditions = selection.toCol;
		for ( itemIndex = selection.fromCol; itemIndex <= indexAfterAdditions; itemIndex++ ) {
			cells = matrix.getColumn( itemIndex );
			cells = cells.map( ( ce ) => {
				// This will set `cell.data` to "data", eg. `{tableCell}data{/tableCell}`. This is expected by
				// "insertRowOrCol" method.
				if ( ce && !ce.isPlaceholder() ) {
					ce.data = surfaceModel.getDocument().getData( ce.node.getOuterRange(), true );
				}
				return ce;
			} );
			this.insertRowOrCol( tableNode, mode, itemIndex, 'after', null, { cells: cells } );
			itemIndex++;
			indexAfterAdditions++;
		}
	}
	matrix.update();
	this.restoreComplexSelection( surfaceModel, selection, tableNode, oldSelection );
};

bs.vec.ui.TableAction.prototype.cellBorder = function ( args ) {
	const surfaceModel = this.surface.getModel();
	const selection = surfaceModel.getSelection();
	const tableNode = selection.getTableNode( surfaceModel.getDocument() );
	const oldSelection = [ selection.fromCol, selection.fromRow, selection.toCol, selection.toRow ];
	const handler = bs.vec.registry.TableStyle.registry.cellBorder;

	if ( args.hasOwnProperty( 'value' ) ) {
		// Normalization
		args = args.value;
	}

	const isCollapsed = args.isCollapsed || true;

	// Get all cells that are targeted with this action, decorating them with appropriate attributes
	// This method will be called recursively internally allowing setting multiple properties on one cell -
	// eg. top left corner cell need to have both left and top border. This method allows being called
	// multiple times, once for top and once for left, and returns single cell that has both top and left set
	const targets = this.getTargetCells( selection, args, isCollapsed );

	this.execBorderStyle( targets, surfaceModel, selection, handler.executeAction.bind( handler ), args );

	this.restoreComplexSelection( surfaceModel, selection, tableNode, oldSelection );
};

bs.vec.ui.TableAction.prototype.getTargetCells = function ( selection, args, isCollapsed ) {
	const mode = args.mode;
	let cells = [];
	// Clone selection, so that recursive calls don't screw it up
	selection = Object.assign( {}, selection );

	// For modes "clear" and "none" all cells are targeted anyway, so no need to pre-clear them
	if ( mode === 'clear' || mode === 'none' && !args.internal ) {
		if ( mode === 'none' ) {
			// Reset all properties when settings no borders, as it might produce unexpected
			// results if some borders are set later on
			cells = this.getTargetCells( selection, { prop: {
				style: 'none',
				width: null,
				color: null
			}, mode: 'none', internal: true }, isCollapsed );
			return cells;
		}
		// If we are not explicitly setting no borders, just clear out borders for all non-targeted cell sides
		cells = this.getTargetCells( selection, { prop: { style: 'none' }, mode: 'none', internal: true }, isCollapsed );
	}

	switch ( mode ) {
		case 'left':
			cells = cells.concat( this.getCol( selection, selection.startCol, args ) );
			if ( isCollapsed ) {
				// In a table with collapsed borders, left border of a cell is actually the right border of the cell to the left
				// Due to that we need to set the styling to both left border of the cell we are actually targeting
				// and to the right border of the cell left of it. Same is true for other borders.
				cells = cells.concat( this.getCol( selection, selection.startCol - 1, Object.assign( args, {
					mode: 'right',
					adjacent: true
				} ) ) );
			}
			break;
		case 'right':
			cells = cells.concat( this.getCol( selection, selection.endCol, args ) );
			if ( isCollapsed ) {
				cells = cells.concat( this.getCol( selection, selection.endCol + 1, Object.assign( args, {
					mode: 'left',
					adjacent: true
				} ) ) );
			}
			break;
		case 'top':
			cells = cells.concat( this.getRow( selection, selection.startRow, args ) );
			if ( isCollapsed ) {
				cells = cells.concat( this.getRow( selection, selection.startRow - 1, Object.assign( args, {
					mode: 'bottom',
					adjacent: true
				} ) ) );
			}
			break;
		case 'bottom':
			cells = cells.concat( this.getRow( selection, selection.endRow, args ) );
			if ( isCollapsed ) {
				cells = cells.concat( this.getRow( selection, selection.endRow + 1, Object.assign( args, {
					mode: 'top',
					adjacent: true
				} ) ) );
			}
			break;
		case 'topbottom':
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'top', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'bottom', internal: true } ), isCollapsed ) );
			break;
		case 'leftright':
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'left', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'right', internal: true } ), isCollapsed ) );
			break;
		case 'round':
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'left', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'right', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'top', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'bottom', internal: true } ), isCollapsed ) );
			break;
		case 'horizontal':
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'top', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, {
				mode: 'inner', includeEdges: true, internal: true, applyMode: 'bottom'
			} ), isCollapsed ) );
			break;
		case 'roundhorizontal':
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'round', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'horizontal', internal: true } ), isCollapsed ) );
			break;
		case 'roundvertical':
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'round', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, {
				mode: 'inner', includeEdges: false, internal: true, applyMode: 'right'
			} ), isCollapsed ) );
			break;
		case 'inner':
			var applyMode = args.applyMode || 'all', i = 0; // eslint-disable-line no-var

			for ( i = selection.startRow; i <= selection.endRow; i++ ) {
				cells = cells.concat( this.getRow( selection, i, Object.assign( args, { mode: applyMode } ) ) );
			}
			break;
		case 'all':
		case 'none':
		case 'clear':
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'round', internal: true } ), isCollapsed ) );
			cells = cells.concat( this.getTargetCells( selection, Object.assign( args, { mode: 'inner', internal: true } ), isCollapsed ) );
			break;
	}

	cells = this.mergeCellProperties( cells );
	return cells;
};

bs.vec.ui.TableAction.prototype.getCol = function ( selection, colIndex, args, cells ) {
	return this.getTableLine( 'col', selection, colIndex, args, cells );
};

bs.vec.ui.TableAction.prototype.getRow = function ( selection, rowIndex, args, cells ) {
	return this.getTableLine( 'row', selection, rowIndex, args, cells );
};

bs.vec.ui.TableAction.prototype.getTableLine = function ( type, selection, index, args, cells ) {
	cells = cells || [];
	args = this.parseArgs( args );

	const result = [];
	let i;
	if ( type === 'row' ) {
		if ( index < 0 ) {
			return result;
		}
		for ( i = selection.startCol; i <= selection.endCol; i++ ) {
			if ( this.getCellIndex( index, i, cells ) !== -1 ) {
				continue;
			}
			result.push( Object.assign( {
				row: index,
				col: i
			}, args ) );
		}
	} else {
		if ( index < 0 ) {
			return result;
		}
		for ( i = selection.startRow; i <= selection.endRow; i++ ) {
			if ( this.getCellIndex( i, index, cells ) !== -1 ) {
				continue;
			}
			result.push( Object.assign( {
				row: i,
				col: index
			}, args ) );
		}
	}
	return result;
};

bs.vec.ui.TableAction.prototype.mergeCellProperties = function ( cells ) {
	const newCells = [];
	for ( let i = 0; i < cells.length; i++ ) {
		const existingIndex = this.getCellIndex( cells[ i ].row, cells[ i ].col, newCells );
		if ( existingIndex === -1 ) {
			newCells.push( cells[ i ] );
		} else {
			newCells[ existingIndex ] = Object.assign( newCells[ existingIndex ], cells[ i ] );
		}
	}
	return newCells;
};

bs.vec.ui.TableAction.prototype.getCellIndex = function ( row, col, cells ) {
	for ( let i = 0; i < cells.length; i++ ) {
		if ( cells[ i ].row === row && cells[ i ].col === col ) {
			return i;
		}
	}
	return -1;
};

bs.vec.ui.TableAction.prototype.parseArgs = function ( args ) {
	const res = {};
	const basePositions = [ 'left', 'right', 'top', 'bottom' ];
	let i;
	if ( basePositions.indexOf( args.mode ) !== -1 ) {
		// Only set current mode, others will be filled from existing props
		res[ args.mode ] = args.prop;
		return res;
	}
	if ( args.mode === 'all' || args.mode === 'none' || args.mode === 'clear' ) {
		for ( i = 0; i < basePositions.length; i++ ) {
			res[ basePositions[ i ] ] = args.prop;
		}
		return res;
	}
};

bs.vec.ui.TableAction.prototype.execBorderStyle = function ( targets, surfaceModel, selection, cb, args ) { // eslint-disable-line no-unused-vars
	const tableNode = selection.getTableNode( surfaceModel.getDocument() ),
		matrix = tableNode.getMatrix();

	for ( let i = 0; i < targets.length; i++ ) {
		let cell = matrix.getCell( targets[ i ].row, targets[ i ].col );
		if ( !cell ) {
			continue;
		}
		cell = cb( cell, targets[ i ] );
		cell.node.reportChanged();
	}
};

bs.vec.ui.TableAction.prototype.restoreComplexSelection = function ( surfaceModel, selection, tableNode, oldSelection ) {
	surfaceModel.setSelection( new ve.dm.TableSelection(
		// tableNode range was changed by deletion
		tableNode.getOuterRange(),
		oldSelection[ 0 ], oldSelection[ 1 ], oldSelection[ 2 ], oldSelection[ 3 ]
	) );
};

ve.ui.actionFactory.register( bs.vec.ui.TableAction );
