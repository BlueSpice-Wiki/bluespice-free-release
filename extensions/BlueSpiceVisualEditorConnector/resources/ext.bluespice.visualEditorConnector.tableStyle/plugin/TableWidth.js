bs.util.registerNamespace( 'bs.ui.plugin' );

bs.ui.plugin.TableWidth = function ( config ) {
	bs.ui.plugin.TableWidth.super.call( this, config );
};

// Keeping it for backwards compatibility
ve.dm.MWTableNode.static.classAttributes.tablefullwidth = { tablefullwidth: true };

OO.inheritClass( bs.ui.plugin.TableWidth, bs.vec.ui.plugin.MWTableDialog );

bs.ui.plugin.TableWidth.prototype.initialize = function () {
	this.component.widthSlider = new OOJSPlus.ui.widget.RangeWidget( {
		min: 0,
		max: 100,
		valueMask: '%v %',
		nullValue: ve.msg( 'bs-vec-ve-table-width-value-auto' )
	} );

	this.widthLayout = new OO.ui.FieldLayout( this.component.widthSlider, {
		align: 'left',
		label: ve.msg( 'bs-vec-ve-table-width-label' )
	} );

	this.component.widthSlider.connect( this, { change: 'onWidthChange' } );
	this.component.panel.$element.prepend( this.widthLayout.$element );
};

bs.ui.plugin.TableWidth.prototype.onWidthChange = function () {
	this.widthChanged = true;
	this.component.updateActions();
};

/**
 * Width as CSS value. Until the slider is moved, a width the slider cannot
 * express (e.g. "880px") is kept as is.
 *
 * @return {string} Empty string for "auto"
 */
bs.ui.plugin.TableWidth.prototype.getWidth = function () {
	if ( !this.widthChanged && this.foreignWidth ) {
		return this.foreignWidth;
	}
	const value = this.component.widthSlider.getValue();
	return value > 0 ? value.toString() + '%' : '';
};

/**
 * Rendered width of the table relative to its container, to place the slider
 * for widths given in other units than %.
 *
 * @param {ve.dm.Node} tableNode
 * @return {number}
 */
bs.ui.plugin.TableWidth.prototype.getRenderedPercentage = function ( tableNode ) {
	const surface = ve.init.target.getSurface();
	if ( !surface ) {
		return 100;
	}
	const ceNode = surface.getView().getDocument()
		.getBranchNodeFromOffset( tableNode.getOuterRange().start + 1 );
	const table = ceNode && ceNode.$element.find( 'table' ).addBack( 'table' )[ 0 ];
	if ( !table || !table.parentElement || !table.parentElement.clientWidth ) {
		return 100;
	}
	return Math.min( 100, Math.round( table.offsetWidth / table.parentElement.clientWidth * 100 ) );
};

bs.ui.plugin.TableWidth.prototype.getValues = function ( values ) {
	return ve.extendObject( values, {
		tablewidth: this.getWidth()
	} );
};

bs.ui.plugin.TableWidth.prototype.getSetupProcess = function ( parentProcess, data ) { // eslint-disable-line no-unused-vars
	parentProcess.next( function () {
		this.fragment = this.component.getFragment();
		if ( !this.fragment ) {
			return;
		}

		const tableNode = this.component.getFragment().getSelection().getTableNode( this.fragment.document );
		const rawWidth = ( tableNode.getAttribute( 'tablewidth' ) || '' ).trim();
		const isPercent = /^\d+(\.\d+)?\s*%$/.test( rawWidth );
		let tableWidth = isPercent ? parseInt( rawWidth ) : 0;

		this.widthChanged = false;
		this.foreignWidth = rawWidth && !isPercent ? rawWidth : '';

		// Backwards compatibility
		if ( tableNode.getAttribute( 'tablefullwidth' ) ) {
			tableWidth = 100;
			this.foreignWidth = '';
		}

		if ( this.foreignWidth ) {
			this.component.widthSlider.setValue( this.getRenderedPercentage( tableNode ) );
			this.component.widthSlider.$value.text( this.foreignWidth );
		} else {
			this.component.widthSlider.setValue( tableWidth );
		}

		ve.extendObject( this.component.initialValues, {
			tablewidth: this.getWidth()
		} );
	}, this );
	return parentProcess;
};

bs.ui.plugin.TableWidth.prototype.getActionProcess = function ( parentProcess, action ) {
	parentProcess.next( function () {
		let surfaceModel, fragment, initialFragment;
		if ( action === 'done' ) {
			initialFragment = this.fragment;
			if ( !initialFragment || !this.widthChanged ) {
				return;
			}
			surfaceModel = initialFragment.getSurface();
			fragment = surfaceModel.getLinearFragment(
				initialFragment.getSelection().tableRange, true
			);

			fragment.changeAttributes( {
				tablewidth: this.getWidth() || false,
				// Remove old class
				tablefullwidth: false
			} );
		}
	}, this );
	return parentProcess;
};

bs.vec.registerComponentPlugin(
	bs.vec.components.TABLE_DIALOG,
	( component ) => new bs.ui.plugin.TableWidth( component )
);
