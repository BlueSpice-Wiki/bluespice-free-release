mws = window.mws || {};
mws.dynamicFileDispatcher = {
	getUrl: function ( module, params ) {
		let url = mw.util.wikiScript( 'rest' ) +
		'/mws/v1/dynamic-file-dispatcher/' + module;
		if ( params && params.length > 0 ) {
			url += '?' + $.param( params );
		}
		return url;
	}
};
