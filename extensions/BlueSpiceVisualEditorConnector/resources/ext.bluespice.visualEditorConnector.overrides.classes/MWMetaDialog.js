bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.MWMetaDialog = function BsVecUiMWMetaDialog( config ) {
	bs.vec.ui.MWMetaDialog.super.call( this, ve.extendObject( { padded: false }, config ) );
};

OO.inheritClass( bs.vec.ui.MWMetaDialog, ve.ui.MWMetaDialog );

bs.vec.ui.MWMetaDialog.prototype.initialize = function () {
	this.initComponentPlugins();
	bs.vec.ui.MWMetaDialog.super.prototype.initialize.call( this );

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		plugin.initialize();
	}
	this.emit( 'initComplete' );
};

bs.vec.ui.MWMetaDialog.prototype.initComponentPlugins = function () {
	this.componentPlugins = [];

	const pluginCallbacks = bs.vec.getComponentPlugins(
		bs.vec.components.META_DIALOG
	);

	for ( let i = 0; i < pluginCallbacks.length; i++ ) {
		const callback = pluginCallbacks[ i ];
		this.componentPlugins.push( callback( this ) );
	}
};

bs.vec.ui.MWMetaDialog.prototype.getSetupProcess = function ( data ) {
	let parentProcess = bs.vec.ui.MWMetaDialog.super.prototype.getSetupProcess.call( this, data );
	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		parentProcess = plugin.getSetupProcess( parentProcess, data );
	}
	return parentProcess;
};

bs.vec.ui.MWMetaDialog.prototype.getTeardownProcess = function ( data ) {
	let parentProcess = bs.vec.ui.MWMetaDialog.super.prototype.getTeardownProcess.call( this, data );

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		parentProcess = plugin.getTeardownProcess( parentProcess, data );
	}

	return parentProcess;

};
