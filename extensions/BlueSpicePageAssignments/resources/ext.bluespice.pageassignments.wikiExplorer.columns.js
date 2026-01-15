mw.hook( 'bs.wikiexplorer.oojs.columns' ).add( ( columns ) => {
	columns.page_assignments = { // eslint-disable-line camelcase
		headerText: mw.message( 'pageassignments' ).escaped(),
		type: 'text',
		valueParser: function ( val ) {
			if ( val !== '' ) {
				return new OO.ui.HtmlSnippet( val );
			}
		},
		filter: {
			type: 'text'
		},
		width: 200,
		hidden: true,
		sortable: true
	};
} );
