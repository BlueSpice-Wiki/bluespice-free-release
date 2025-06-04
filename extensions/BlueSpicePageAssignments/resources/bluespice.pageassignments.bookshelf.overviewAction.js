( function ( mw, $, d ) {

	function getPageId( pageName ) {
		const dfd = $.Deferred();
		const api = new mw.Api();
		api.get( {
			action: 'query',
			titles: pageName,
			format: 'json',
			prop: 'info'
		} ).fail( ( e ) => {
			dfd.reject( e );
		} )
			.done( ( resp ) => {
				const pages = resp.query.pages;
				let pageId = 0;
				for ( const page in pages ) {
					pageId = pages[ page ].pageid;
				}
				dfd.resolve( pageId );
			} );

		return dfd.promise();
	}

	function _showDialog( page ) { // eslint-disable-line no-underscore-dangle
		const dfd = $.Deferred();

		getPageId( page ).done( ( pageID ) => {
			const api = new mw.Api();
			api.postWithToken( 'csrf', {
				action: 'bs-pageassignment-tasks',
				formatversion: 2,
				task: 'getForPage',
				taskData: JSON.stringify( {
					pageId: pageID
				} )
			} ).done( ( response ) => {
				if ( response.success ) {
					dfd.resolve( [
						new bs.pageassignments.ui.AssignmentsPage( {
							data: {
								page: pageID,
								assignments: response.payload
							}
						} )
					] );
				} else {
					dfd.reject();
				}
			} ).fail( ( e ) => {
				dfd.reject( e );
			} );
		} );

		const dialog = new OOJSPlus.ui.dialog.BookletDialog( {
			id: 'bs-pageassignments-set',
			pages: function () {
				return dfd.promise();
			}
		} );

		dialog.show();
	}

	$( d ).on( 'click', '.pageassignments-book-overview', ( e ) => {
		e.preventDefault();
		let target = e.target;
		if ( target.nodeName !== 'A' ) {
			target = $( target ).parent(); // eslint-disable-line no-jquery/variable-pattern
		}

		const page = $( target ).data( 'prefixed_db_key' );
		mw.loader.using( 'ext.bluespice.pageassignments.dialog.pages' ).done( _showDialog( page ) );
	} );
}( mediaWiki, jQuery, document ) );
