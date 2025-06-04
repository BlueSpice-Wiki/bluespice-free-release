( function ( mw, $, d ) {
	function _showDialog() { // eslint-disable-line no-underscore-dangle
		const dfd = $.Deferred();

		const api = new mw.Api();
		api.postWithToken( 'csrf', {
			action: 'bs-pageassignment-tasks',
			formatversion: 2,
			task: 'getForPage',
			taskData: JSON.stringify( {
				pageId: mw.config.get( 'wgArticleId' )
			} )
		} ).done( ( response ) => {
			if ( response.success ) {
				dfd.resolve( [
					new bs.pageassignments.ui.AssignmentsPage( {
						data: {
							page: mw.config.get( 'wgArticleId' ),
							assignments: response.payload
						}
					} )
				] );
			} else {
				dfd.reject();
			}
		} ).fail( () => {
			dfd.reject();
		} );

		const dialog = new OOJSPlus.ui.dialog.BookletDialog( {
			id: 'bs-pageassignments-set',
			pages: function () {
				return dfd.promise();
			}
		} );

		dialog.show();
	}

	$( d ).on( 'click', '#ca-pageassignments a, a#ca-pageassignments', ( e ) => {
		e.preventDefault();
		mw.loader.using( 'ext.bluespice.pageassignments.dialog.pages' ).done( _showDialog );
	} );
}( mediaWiki, jQuery, document ) );
