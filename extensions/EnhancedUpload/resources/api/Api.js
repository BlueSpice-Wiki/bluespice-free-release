window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.api = window.enhancedUpload.api || {};

enhancedUpload.api.Api = function () {
};

OO.initClass( enhancedUpload.api.Api );

enhancedUpload.api.Api.prototype.post = function ( path, params ) {
	params = params || {};
	return this.ajax( path, JSON.stringify( { files: params } ), 'POST' );
};

enhancedUpload.api.Api.prototype.makeUrl = function ( path ) {
	if ( path.charAt( 0 ) === '/' ) {
		path = path.slice( 1 );
	}

	return mw.util.wikiScript( 'rest' ) + '/enhancedupload/' + path;
};

enhancedUpload.api.Api.prototype.ajax = function ( path, data, method ) {
	const dfd = $.Deferred();
	data = data || {};

	$.ajax( {
		method: method,
		url: this.makeUrl( path ),
		data: data,
		contentType: 'application/json',
		dataType: 'json'
	} ).done( ( response ) => {
		if ( response.success === true ) {
			dfd.resolve( response );
		}
		dfd.reject( { status: response.status } );
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

enhancedUpload.api.Api.prototype.addFiles = function ( pageId, tagcounter, data, pageNames ) {
	const files = [];
	let item;
	for ( item in pageNames ) {
		files.push( pageNames[ item ] );
	}
	return this.post( 'addattachments/' + pageId + '/' + tagcounter, files );
};

enhancedUpload.api.Api.prototype.removeFiles = function ( pageId, tagcounter, data ) {
	return this.post( 'removeattachments/' + pageId + '/' + tagcounter, data );
};

enhancedUpload.api.Api.prototype.addLink = function ( pageId, tagcounter, data ) {
	return this.post( 'addattachments/' + pageId + '/' + tagcounter, data );
};
