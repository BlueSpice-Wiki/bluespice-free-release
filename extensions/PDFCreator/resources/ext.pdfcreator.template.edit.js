'use strict';

( function () {

	$( document ).on( 'click', '.new-template-action', ( e ) => {
		e.preventDefault();
		mw.loader.using( [ 'ext.pdfcreator.export.api' ] ).done( () => {
			const api = new ext.pdfcreator.api.Api();
			api.getTemplateValues().done( ( data ) => {
				const templateValues = data.values;
				if ( templateValues.length === 0 ) {
					return;
				}
				mw.loader.using( [ 'ext.pdfcreator.template.edit.dialog' ] ).done( () => {
					const dialog = new ext.pdfcreator.ui.dialog.EditDialog( {
						data: templateValues,
						params: data.params
					} );
					dialog.connect( this, {
						saved: function () {
							window.location.reload();
						}
					} );
					const windowManager = new OO.ui.WindowManager( {
						modal: true
					} );
					$( document.body ).append( windowManager.$element );
					windowManager.addWindows( [ dialog ] );
					windowManager.openWindow( dialog );
				} );
			} );
		} );
	} );
}() );
