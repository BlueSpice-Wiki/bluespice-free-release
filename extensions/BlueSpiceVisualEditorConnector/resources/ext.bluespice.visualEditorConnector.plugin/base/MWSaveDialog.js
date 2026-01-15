bs.util.registerNamespace( 'bs.vec.ui.plugin' );

bs.vec.ui.plugin.MWSaveDialog = function BsVecUiPluginMWSaveDialog( component ) {
	this.component = component;
};

OO.initClass( bs.vec.ui.plugin.MWSaveDialog );

bs.vec.ui.plugin.MWSaveDialog.prototype.initialize = function () {
	// do nothing
};

bs.vec.ui.plugin.MWSaveDialog.prototype.getSetupProcess = function ( parentProcess, data ) { // eslint-disable-line no-unused-vars
	return parentProcess;
};

bs.vec.ui.plugin.MWSaveDialog.prototype.getActionProcess = function ( parentProcess, action ) { // eslint-disable-line no-unused-vars
	return parentProcess;
};

bs.vec.ui.plugin.MWSaveDialog.prototype.swapPanel = function ( panel, noFocus ) { // eslint-disable-line no-unused-vars
	// do nothing
};
