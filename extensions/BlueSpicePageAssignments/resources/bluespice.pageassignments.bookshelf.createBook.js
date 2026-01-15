mw.hook( 'bs.bookshelf.newbook.actionprocess' ).add( ( process, dialog ) => process.next( () => {
	const mwApi = new mw.Api();
	mwApi.get( {
		action: 'query',
		titles: dialog.bookTitle,
		format: 'json',
		prop: 'info'
	} ).fail( function () {
		dialog.showErrors( new OO.ui.Error( arguments[ 0 ], { recoverable: false } ) );
	} )
		.done( ( resp ) => {
			const pages = resp.query.pages;
			let pageId = 0;
			for ( const page in pages ) {
				pageId = pages[ page ].pageid;
			}
			blueSpice.api.tasks.exec(
				'pageassignment',
				'edit',
				{
					pageId: pageId,
					pageAssignments: [ 'user/' + mw.user.getName() ]
				}, {
					success: function () {},
					failure: function ( response ) {
						dialog.showErrors( new OO.ui.Error( response ) );
					}
				}
			);
		} );
} ) );
