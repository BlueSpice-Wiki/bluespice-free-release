mws = window.mws || {};
mws.tokenAuthenticator = {
	generateToken: ( withIssuer ) =>  {
		const dfd = $.Deferred();
		mw.loader.using( 'mediawiki.util' ).then( () => {
			$.ajax( {
				url: mw.util.wikiScript( 'rest' ) + '/mws/v1/user-token/generate?withIssuer=' + ( withIssuer ? 'true' : 'false' ),
				dataType: 'json',
				type: 'GET',
				contentType: 'application/json; charset=utf-8'
			} ).done(  ( data ) => {
				if ( data && data.value ) {
					dfd.resolve( data.value );
				} else {
					dfd.reject();
				}
			} ).fail( () => {
				dfd.reject();
			} );
		}, () => { dfd.reject(); } );
		return dfd.promise();
	}
};