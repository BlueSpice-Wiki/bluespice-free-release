'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.api = ext.pdfcreator.api || {};

ext.pdfcreator.api.Api = function () {

};

OO.initClass( ext.pdfcreator.api.Api );

ext.pdfcreator.api.Api.prototype.ajax = function ( path, data, method ) {
	data = data || {};
	const dfd = $.Deferred();

	$.ajax( {
		method: method,
		url: this.makeUrl( path ),
		data: data,
		contentType: 'application/json',
		dataType: 'json'
	} ).done( ( response ) => {
		if ( typeof response === 'object' && response.success === false ) {
			dfd.reject( response.status );
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

ext.pdfcreator.api.Api.prototype.makeUrl = function ( path ) {
	if ( path.charAt( 0 ) === '/' ) {
		path = path.slice( 1 );
	}
	return mw.util.wikiScript( 'rest' ) + '/pdfcreator/' + path;
};

ext.pdfcreator.api.Api.prototype.post = function ( path, params ) {
	params = params || {};
	return this.ajax( path, JSON.stringify( { data: params } ), 'POST' );
};

ext.pdfcreator.api.Api.prototype.get = function ( path ) {
	return this.ajax( path, '', 'GET' );
};

ext.pdfcreator.api.Api.prototype.save = function ( id, data ) {
	data = data || {};
	return this.post( 'savetemplate/' + id, data );
};

ext.pdfcreator.api.Api.prototype.getPageExportModes = function ( id ) {
	return this.get( 'getrelevantmodes/' + id );
};

ext.pdfcreator.api.Api.prototype.export = function ( id, data ) {
	data = data || {};
	const dfd = $.Deferred();

	this.doExport( 'export/' + id + '/' + JSON.stringify( data ), 'GET' )
		.done( async ( response, statusText, jqXHR ) => {
			const filename = jqXHR.getResponseHeader( 'X-Filename' ) || mw.config.get( 'wgPageName' ) + '.pdf';

			const url = window.URL.createObjectURL( response );
			const a = document.createElement( 'a' );
			a.href = url;
			a.download = filename;
			document.body.appendChild( a );
			a.click();
			a.remove();
			window.URL.revokeObjectURL( url );
			dfd.resolve();
		} ).fail( ( errorMessage ) => {
			dfd.reject( errorMessage );
		} );
	return dfd.promise();
};

ext.pdfcreator.api.Api.prototype.doExport = function ( path, method ) {
	const dfd = $.Deferred();

	$.ajax( {
		method: method,
		url: this.makeUrl( path ),
		contentType: 'application/json',
		accept: 'application/pdf',
		xhrFields: {
			responseType: 'blob'
		},
		success: ( response, statusText, jqXHR ) => {
			dfd.resolve( response, statusText, jqXHR );
		},
		error: ( jqXHR ) => {
			const errorDetails = jqXHR.getResponseHeader( 'X-Error-Details' );
			dfd.reject( errorDetails );
		}
	} );
	return dfd.promise();
};

ext.pdfcreator.api.Api.prototype.getTemplates = function () {
	return this.get( 'templates' );
};

ext.pdfcreator.api.Api.prototype.getTemplateValues = function ( template = '' ) {
	return this.get( 'templatevalues/' + template );
};

ext.pdfcreator.api.Api.prototype.saveEdit = function ( template, data ) {
	return this.post( 'saveedit/' + template, data );
};
