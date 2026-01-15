window.enhancedUpload = window.enhancedUpload || {};

enhancedUpload.ParamsProcessor = function () {};

OO.initClass( enhancedUpload.ParamsProcessor );

// eslint-disable-next-line no-unused-vars
enhancedUpload.ParamsProcessor.prototype.setDefaultPrefix = function ( prefix ) {
	// STUB
};

// allows modification for upload params
// eslint-disable-next-line no-unused-vars
enhancedUpload.ParamsProcessor.prototype.getParams = function ( params, item, skipOption ) {
	if ( params.filename === item.name && params.prefix !== '' ) {
		// eslint-disable-next-line no-underscore-dangle
		this._getParamFileName( params, params.prefix );
	}

	return params;
};

// eslint-disable-next-line no-underscore-dangle
enhancedUpload.ParamsProcessor.prototype._getParamFileName = function ( params, prefix ) {
	const filename = params.filename;
	let prefixPartOne = '';
	const prefixParts = prefix.split( ':' );

	if ( prefixParts.length > 1 ) {
		prefixPartOne = prefixParts[ 0 ];
		prefixParts.splice( 0, 1 );
	}

	let prefixStub = prefixParts.join( ':' );
	prefixStub = prefixStub.replace( ':', '_' );

	if ( prefixPartOne.length > 0 ) {
		prefixStub = prefixPartOne + '_' + prefixStub;
	}

	if ( prefixStub === filename.slice( 0, prefixStub.length ) ) {
		prefixStub = '';
	}

	params.filename = prefixStub + filename;
};
