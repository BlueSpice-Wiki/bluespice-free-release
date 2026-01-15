( function ( mw, $, d ) {
	$( d ).on( 'click', '#ca-bs-qr-code', ( e ) => {
		e.preventDefault();

		const windowManager = OO.ui.getWindowManager();
		const pageName = mw.config.get( 'wgPageName' );
		const params = {
			action: 'bs-qr-code',
			page: pageName
		};

		const api = new mw.Api();
		api.get( params ).done( ( response ) => {
			const dialog = new bs.ui.dialog.QrCodeDialog( {
				titleMsg: 'bs-qr-code-action-qr-code-dialog-title',
				altMsg: 'bs-qr-code-action-qr-code-dialog-alt',
				page: pageName,
				data: response.data
			} );
			windowManager.addWindows( [ dialog ] );
			windowManager.openWindow( dialog );
		} );
	} );
}( mediaWiki, jQuery, document ) );
