bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.MWTableDialog = function BsVecUiMWTableDialog( config ) {
	bs.vec.ui.MWTableDialog.super.call( this, config );
};

OO.inheritClass( bs.vec.ui.MWTableDialog, ve.ui.MWTableDialog );

bs.vec.ui.MWTableDialog.prototype.initialize = function () {
	bs.vec.ui.MWTableDialog.super.prototype.initialize.call( this );

	const $properties = this.panel.$element.children();
	/* set switch 'styled wikitable' from VE hidden  */
	for ( let i = 0; i < $properties.length; i++ ) {
		if ( $properties[ i ].innerText.includes( 'wikitable' ) ) {
			this.panel.$element.children().eq( i ).hide();
		}
	}

	this.initComponentPlugins();

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		plugin.initialize();
	}

	this.panel.$element.append( $properties );
};

bs.vec.ui.MWTableDialog.prototype.getValues = function () {
	let values = bs.vec.ui.MWTableDialog.super.prototype.getValues.call( this );

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		values = plugin.getValues( values );
	}
	return values;
};

bs.vec.ui.MWTableDialog.prototype.getSetupProcess = function ( data ) {
	let parentProcess = bs.vec.ui.MWTableDialog.super.prototype.getSetupProcess.call( this, data );
	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		parentProcess = plugin.getSetupProcess( parentProcess, data );
	}
	return parentProcess;
};

bs.vec.ui.MWTableDialog.prototype.getActionProcess = function ( action ) {
	const parentProcess = bs.vec.ui.MWTableDialog.super.prototype.getActionProcess.call( this, action );
	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		plugin.getActionProcess( parentProcess, action );
	}
	return parentProcess;
};

bs.vec.ui.MWTableDialog.prototype.initComponentPlugins = function () {
	this.componentPlugins = [];

	const pluginCallbacks = bs.vec.getComponentPlugins(
		bs.vec.components.TABLE_DIALOG
	);

	for ( let i = 0; i < pluginCallbacks.length; i++ ) {
		const callback = pluginCallbacks[ i ];
		this.componentPlugins.push( callback( this ) );
	}
};
