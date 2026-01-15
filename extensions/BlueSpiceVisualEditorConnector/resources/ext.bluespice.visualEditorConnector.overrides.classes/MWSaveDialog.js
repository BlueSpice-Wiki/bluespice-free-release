bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.MWSaveDialog = function BsVecUiMWSaveDialog() {
	bs.vec.ui.MWSaveDialog.super.apply( this, arguments );
};

OO.inheritClass( bs.vec.ui.MWSaveDialog, ve.ui.MWSaveDialog );

bs.vec.ui.MWSaveDialog.prototype.initialize = function () {
	bs.vec.ui.MWSaveDialog.super.prototype.initialize.call( this );

	this.initComponentPlugins();

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		plugin.initialize();
	}
};

bs.vec.ui.MWSaveDialog.prototype.initComponentPlugins = function () {
	this.componentPlugins = [];

	const pluginCallbacks = bs.vec.getComponentPlugins(
		bs.vec.components.SAVE_DIALOG
	);

	for ( let i = 0; i < pluginCallbacks.length; i++ ) {
		const callback = pluginCallbacks[ i ];
		this.componentPlugins.push( callback( this ) );
	}
};

bs.vec.ui.MWSaveDialog.prototype.getSetupProcess = function ( data ) {
	let parentProcess = bs.vec.ui.MWSaveDialog.super.prototype.getSetupProcess.call( this, data );
	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		parentProcess = plugin.getSetupProcess( parentProcess, data );
	}
	return parentProcess;
};

bs.vec.ui.MWSaveDialog.prototype.getActionProcess = function ( action ) {
	const parentProcess = bs.vec.ui.MWSaveDialog.super.prototype.getActionProcess.apply( this, arguments );

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		plugin.getActionProcess( parentProcess, action );
	}

	if ( action === 'save' ) {
		parentProcess.next( () => {
			window.location.reload();
		} );
	}

	if ( action === 'approve' ) {
		return new OO.ui.Process( function () {
			this.swapPanel( 'save' );
			this.emit( action );
		}, this );
	}

	return parentProcess;
};

bs.vec.ui.MWSaveDialog.prototype.swapPanel = function ( panel, noFocus ) {
	bs.vec.ui.MWSaveDialog.super.prototype.swapPanel.apply( this, arguments );

	this.emit( 'swapPanelComplete', panel );

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		plugin.swapPanel( panel, noFocus );
	}
};
