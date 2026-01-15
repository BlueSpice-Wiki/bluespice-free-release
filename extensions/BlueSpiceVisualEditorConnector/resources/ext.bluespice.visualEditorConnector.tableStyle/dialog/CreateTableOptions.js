bs.util.registerNamespace( 'bs.vec.ui.dialog' );

bs.vec.ui.dialog.CreateTableOptions = function () {
	bs.vec.ui.dialog.CreateTableOptions.parent.call( this, {
		size: 'medium'
	} );
};

OO.inheritClass( bs.vec.ui.dialog.CreateTableOptions, OO.ui.ProcessDialog );

bs.vec.ui.dialog.CreateTableOptions.static.name = 'createTableOptions';

bs.vec.ui.dialog.CreateTableOptions.static.actions = [
	{
		action: 'save',
		label: OO.ui.deferMsg( 'bs-visualeditorconnector-dialog-action-insert' ),
		flags: [ 'primary', 'progressive' ]
	},
	{
		title: OO.ui.deferMsg( 'bs-vec-dialog-action-safe' ),
		flags: [ 'safe', 'close' ]
	}
];

bs.vec.ui.dialog.CreateTableOptions.static.title = OO.ui.deferMsg( 'bs-visualeditorconnector-dialog-table-title' );

bs.vec.ui.dialog.CreateTableOptions.prototype.initialize = function () {
	const mainLayout = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	bs.vec.ui.dialog.CreateTableOptions.parent.prototype.initialize.call( this, arguments );

	this.rowsWidget = new OO.ui.NumberInputWidget( {
		min: 0,
		value: 5 // VE default
	} );
	this.columnsWidget = new OO.ui.NumberInputWidget( {
		min: 0,
		value: 4 // VE default
	} );

	mainLayout.$element.append( new OO.ui.FieldLayout( this.rowsWidget, {
		label: OO.ui.deferMsg( 'bs-vec-dialog-table-create-number-of-rows' )
	} ).$element );
	mainLayout.$element.append( new OO.ui.FieldLayout( this.columnsWidget, {
		label: OO.ui.deferMsg( 'bs-vec-dialog-table-create-number-of-columns' )
	} ).$element );
	this.$body.append( mainLayout.$element );
};

bs.vec.ui.dialog.CreateTableOptions.prototype.getActionProcess = function ( action ) {
	if ( action === 'save' ) {
		return new OO.ui.Process( () => {
			this.rowsWidget.getValidity().done( () => {
				this.columnsWidget.getValidity().done( () => {
					this.close( {
						action: action,
						cols: this.columnsWidget.getValue(),
						rows: this.rowsWidget.getValue()
					} );
				} ).fail( () => {
					this.columnsWidget.setValidityFlag( false );
				} );
			} ).fail( () => {
				this.columnsWidget.setValidityFlag( false );
			} );
		} );
	}
	return bs.vec.ui.dialog.CreateTableOptions.parent.prototype.getActionProcess.call( this, action );
};
