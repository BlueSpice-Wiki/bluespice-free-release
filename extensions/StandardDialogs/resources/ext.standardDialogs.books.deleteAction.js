$( document ).on( 'click', '.bs-books-overview-action-delete', ( e ) => {
	let target = e.target;
	if ( target.nodeName !== 'A' ) {
		target = $( target ).parent();
	}

	const pageName = $( target ).data( 'prefixed_db_key' );
	mw.loader.using( [ 'ext.standardDialogs.ui.DeleteDialog' ] ).done( () => {
		const msg = require( './deleteDialogMsg.json' );

		const diag = new StandardDialogs.ui.DeleteDialog( {
			id: 'standarddialogs-dlg-delete',
			pageName: pageName,
			msg: msg
		} );
		diag.on( 'actioncompleted', () => {
			window.location.reload();
		} );
		diag.show();
	} );
	e.defaultPrevented = true;
	return false;
} );
