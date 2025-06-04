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

	this.component.widthSlider.connect( this.component, { change: 'updateActions' } );
	this.component.panel.$element.prepend( this.widthLayout.$element );
};

bs.ui.plugin.TableWidth.prototype.getValues = function ( values ) {
	return ve.extendObject( values, {
		tablewidth: this.component.widthSlider.getValue().toString() + '%'
	} );
};

bs.ui.plugin.TableWidth.prototype.getSetupProcess = function ( parentProcess, data ) { // eslint-disable-line no-unused-vars
	parentProcess.next( function () {
		this.fragment = this.component.getFragment();
		if ( !this.fragment ) {
			return;
		}

		const tableNode = this.component.getFragment().getSelection().getTableNode( this.fragment.document );
		let tableWidth = tableNode.getAttribute( 'tablewidth' ) ?
			parseInt( tableNode.getAttribute( 'tablewidth' ) ) : 0;

		// Backwards compatibility
		if ( tableNode.getAttribute( 'tablefullwidth' ) ) {
			tableWidth = 100;
		}

		this.component.widthSlider.setValue( tableWidth );

		ve.extendObject( this.component.initialValues, {
			tableWidth: tableWidth.toString() + '%'
		} );
	}, this );
	return parentProcess;
};

bs.ui.plugin.TableWidth.prototype.getActionProcess = function ( parentProcess, action ) {
	parentProcess.next( function () {
		let surfaceModel, fragment, initialFragment;
		if ( action === 'done' ) {
			initialFragment = this.fragment;
			if ( !initialFragment ) {
				return;
			}
			surfaceModel = initialFragment.getSurface();
			fragment = surfaceModel.getLinearFragment(
				initialFragment.getSelection().tableRange, true
			);

			const value = this.component.widthSlider.getValue();
			fragment.changeAttributes( {
				tablewidth: value > 0 ? value.toString() + '%' : false,
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
