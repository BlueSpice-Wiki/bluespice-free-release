bs.util.registerNamespace( 'bs.ui.plugin' );

bs.ui.plugin.TableOptions = function ( config ) {
	this.styleclasses = bs.vec.config.get( 'TableStyleRegistry', {} );

	bs.ui.plugin.TableOptions.super.call( this, config );
};

OO.inheritClass( bs.ui.plugin.TableOptions, bs.vec.ui.plugin.MWTableDialog );

bs.ui.plugin.TableOptions.prototype.initialize = function () {
	const options = [];
	for ( const style in this.styleclasses ) {
		if ( !this.styleclasses.hasOwnProperty( style ) ) {
			continue;
		}

		const classname = this.styleclasses[ style ];
		const option = new OO.ui.MenuOptionWidget( {
			data: classname, label: style
		} );
		options.push( option );
	}
	this.component.TableOptions = new OO.ui.DropdownWidget( {
		label: ve.msg( 'bs-visualeditorconnector-ve-style-option-description' ),
		menu: {
			items: options
		}
	} );
	const tableOptionsLayout = new OO.ui.FieldLayout( this.component.TableOptions, {
		align: 'left',
		label: ve.msg( 'bs-visualeditorconnector-ve-style-option' )
	} );

	this.component.TableOptions.connect( this.component, { change: 'updateActions' } );
	this.component.panel.$element.prepend( tableOptionsLayout.$element );

};

bs.ui.plugin.TableOptions.prototype.getValues = function ( values ) {
	return ve.extendObject( values, { tableoption: this.getActiveClass() } );
};

bs.ui.plugin.TableOptions.prototype.getActiveClass = function ( fragment, fromOriginalClasses ) {
	fragment = fragment || this.component.getFragment();

	const options = Object.assign( {}, fragment.getSelection().getTableNode( fragment.document ).getAttributes() );
	if ( fromOriginalClasses ) {
		if ( !options.hasOwnProperty( 'originalClasses' ) ) {
			return false;
		}
		const originalClasses = options.originalClasses.split( ' ' );
		for ( let x = 0; x < originalClasses.length; x++ ) {
			if (
				Object.values( this.styleclasses ).indexOf( originalClasses[ x ] ) !== -1 &&
				!options.hasOwnProperty( originalClasses[ x ] )
			) {
				options[ originalClasses[ x ] ] = true;
			}
		}
	}

	for ( const style in this.styleclasses ) {
		if ( !this.styleclasses.hasOwnProperty( style ) ) {
			continue;
		}
		if ( !options.hasOwnProperty( this.styleclasses[ style ] ) ) {
			continue;
		}
		if ( options[ this.styleclasses[ style ] ] === true ) {
			return this.styleclasses[ style ];
		}
	}

	if ( !fromOriginalClasses ) {
		return this.getActiveClass( fragment, true );
	}

	return false;
};

bs.ui.plugin.TableOptions.prototype.getSetupProcess = function ( parentProcess ) {
	parentProcess.next( function () {
		this.fragment = this.component.getFragment();
		const activeClass = this.getActiveClass( this.fragment );

		let selected = this.component.TableOptions.getMenu().findItemFromData( 'nostyle' );
		if ( activeClass ) {
			selected = this.component.TableOptions.getMenu().findItemFromData( activeClass );
		}
		this.component.TableOptions.getMenu().selectItem( selected );
		ve.extendObject( this.component.initialValues, {
			tableoption: activeClass
		} );
	}, this );

	return parentProcess;
};

bs.ui.plugin.TableOptions.prototype.getActionProcess = function ( parentProcess, action ) {
	parentProcess.next( function () {
		let initialFragment, surfaceModel, fragment;

		if ( action === 'done' ) {
			initialFragment = this.component.getFragment() || this.fragment;
			if ( !initialFragment ) {
				return;
			}
			surfaceModel = initialFragment.getSurface();
			fragment = surfaceModel.getLinearFragment(
				initialFragment.getSelection().tableRange, true
			);
			const selectedItem = this.component.TableOptions.getMenu().findSelectedItem();
			if ( !selectedItem ) {
				return;
			}
			const selectedClass = this.component.TableOptions.getMenu().findSelectedItem().getData();
			const allItems = this.component.TableOptions.getMenu().getItems();

			allItems.forEach( ( thisClass ) => {
				const thisName = thisClass.data;
				const obj = {};
				if ( thisName === 'nostyle' ) {
					obj[ thisName ] = false;
					fragment.changeAttributes( obj );
				} else if ( thisName === selectedClass ) {
					obj[ thisName ] = true;
					fragment.changeAttributes( obj );
				} else {
					obj[ thisName ] = false;
					fragment.changeAttributes( obj );
				}
			} );
		}
	}, this );
	return parentProcess;
};

bs.vec.registerComponentPlugin(
	bs.vec.components.TABLE_DIALOG,
	( component ) => new bs.ui.plugin.TableOptions( component )
);

( function registerClasses() {
	const styleclasses = bs.vec.config.get( 'TableStyleRegistry' );

	// Add class attributes
	for ( const style in styleclasses ) {
		if ( !styleclasses.hasOwnProperty( style ) ) {
			continue;
		}
		const classname = styleclasses[ style ];
		const obj = {};
		obj[ classname ] = true;
		ve.dm.MWTableNode.static.classAttributes[ classname ] = obj;
	}
}() );
