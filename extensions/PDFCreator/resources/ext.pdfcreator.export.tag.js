'use strict';

( function () {

	$( document ).on( 'click', '.pdfcreator-export', ( e ) => {
		require( './api/Api.js' );
		e.preventDefault();
		const exportConfig = $( e.currentTarget ).data( 'export' );
		const data = {
			mode: exportConfig.mode,
			template: exportConfig.template
		};
		mw.hook( 'pdfcreator.export.data' ).fire( this, data );
		const api = new ext.pdfcreator.api.Api();
		api.export( exportConfig.pageid, data ).done( () => {
			mw.notify( mw.message( 'pdfcreator-notification-pdf-creation-done' ).text() );
		} ).fail( ( error ) => {
			console.log( error ); // eslint-disable-line no-console
		} );
	} );
}() );
