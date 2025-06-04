'use strict';

( function () {

	$( document ).on( 'click', '#pdfcreator-export-dlg', ( e ) => {
		e.preventDefault();
		const config = require( './exportconfig.json' );
		const templates = config.templates;

		require( './ui/dialog/ExportDialog.js' );
		require( './api/Api.js' );
		const api = new ext.pdfcreator.api.Api();
		api.getPageExportModes( mw.config.get( 'wgArticleId' ) ).done( ( response ) => {
			const modes = response.mode;

			const modules = modes.modules;
			const modeLabels = modes.labels;
			const defaults = modes.defaults;
			mw.loader.using( modules ).done( () => {
				const dialog = new ext.pdfcreator.ui.dialog.ExportDialog( {
					templates: templates,
					modes: modeLabels,
					defaultTemplates: defaults
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
}() );
