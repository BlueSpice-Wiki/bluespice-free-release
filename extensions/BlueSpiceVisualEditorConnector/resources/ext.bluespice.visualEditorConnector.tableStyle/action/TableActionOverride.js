var orig = ve.ui.TableAction.prototype.create; // eslint-disable-line no-var

ve.ui.TableAction.prototype.create = function ( options ) {
	const optionsDialog = new bs.vec.ui.dialog.CreateTableOptions(),
		preselection = ve.init.target.surface.model.selection;

	const windowManager = new OO.ui.WindowManager();
	$( document.body ).append( windowManager.$element );
	windowManager.addWindows( [ optionsDialog ] );
	windowManager.openWindow( optionsDialog ).closed.done( ( data ) => {
		if ( !data ) {
			return;
		}
		if ( !data.hasOwnProperty( 'action' ) ) {
			return;
		}
		if ( data.action !== 'save' ) {
			return;
		}
		options.cols = parseInt( data.cols );
		options.rows = parseInt( data.rows );
		ve.init.target.surface.model.selection = preselection;
		return orig.call( this, options );
	} );
};
