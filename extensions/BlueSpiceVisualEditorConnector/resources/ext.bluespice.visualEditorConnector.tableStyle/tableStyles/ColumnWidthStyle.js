bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.ColumnWidthStyle = function () {
	bs.vec.ui.ColumnWidthStyle.super.apply( this );
	this.section = bs.vec.ui.TableStyle.static.SECTION_COLUMN;
	this.applyTo = bs.vec.ui.TableStyle.static.ELEMENT_CELL;
};

OO.inheritClass( bs.vec.ui.ColumnWidthStyle, bs.vec.ui.TableStyle );

bs.vec.ui.ColumnWidthStyle.prototype.getAttribute = function () {
	return 'width';
};

bs.vec.ui.ColumnWidthStyle.prototype.getUnit = function () {
	return bs.vec.ui.TableStyle.static.UNIT_NONE;
};

bs.vec.ui.ColumnWidthStyle.prototype.getTool = function () {
	return {
		widget: bs.vec.ui.widget.ColumnWidthWidget,
		displaySection: bs.vec.ui.TableStyle.static.TYPE_ADDITIONAL,
		label: OO.ui.deferMsg( 'bs-vec-table-style-column-width' )
	};
};

bs.vec.ui.ColumnWidthStyle.prototype.getModelProperty = function () {
	return 'columnWidth';
};

bs.vec.registry.TableStyle.register( 'columnWidth', new bs.vec.ui.ColumnWidthStyle() );
