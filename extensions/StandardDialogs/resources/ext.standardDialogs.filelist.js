mw.hook( 'enhanced.filelist.action' ).add( ( data ) => {
	if ( data.action === 'delete' ) {
		data.action = '';
		mw.loader.using( [ 'ext.standardDialogs.ui.DeleteDialog' ] ).done( () => {
			const msg = require( './deleteDialogMsg.json' );
			const diag = new StandardDialogs.ui.DeleteDialog( {
				id: 'standarddialogs-dlg-delete',
				pageName: 'File:' + data.row.dbkey,
				msg: msg
			} );
			diag.on( 'actioncompleted', () => {
				window.location.reload();
			} );
			diag.show();
		} );
	}
} );
