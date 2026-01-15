ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.api = ext.enhancedUI.api || {};

ext.enhancedUI.api.Api = function () {};

OO.initClass( ext.enhancedUI.api.Api );

ext.enhancedUI.api.Api.prototype.makeUrl = function ( path ) {
	if ( path.charAt( 0 ) === '/' ) {
		path = path.slice( 1 );
	}
	return mw.util.wikiScript( 'rest' ) + '/standarduis/' + path;
};

ext.enhancedUI.api.Api.prototype.get = function ( path, params ) {
	params = params || {};
	return this.ajax( path, params, 'GET' );
};

ext.enhancedUI.api.Api.prototype.ajax = function ( path, data, method ) {
	data = data || {};
	const dfd = $.Deferred();

	$.ajax( {
		method: method,
		url: this.makeUrl( path ),
		data: data,
		contentType: 'application/json',
		dataType: 'json'
	} ).done( ( response ) => {
		if ( response.success === false ) {
			dfd.reject();
			return;
		}
		dfd.resolve( response );
	} ).fail( ( jgXHR, type, status ) => {
		if ( type === 'error' ) {
			dfd.reject( {
				error: jgXHR.responseJSON || jgXHR.responseText
			} );
		}
		dfd.reject( { type: type, status: status } );
	} );

	return dfd.promise();
};

ext.enhancedUI.api.Api.prototype.getFileMetadata = function ( filename ) {
	return this.get( 'file-metadata/' + encodeURIComponent( filename ) );
};

ext.enhancedUI.api.Api.prototype.getNamespaces = function () {
	return this.get( 'namespaces' );
};
