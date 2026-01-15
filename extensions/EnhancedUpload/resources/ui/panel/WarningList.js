window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.panel = window.enhancedUpload.ui.panel || {};

enhancedUpload.ui.panel.WarningList = function enhancedUploadUiPanelWarningList( cfg ) {
	enhancedUpload.ui.panel.WarningList.parent.call( this, cfg );
	this.data = [];

	this.$grid = $( '<div>' );
	this.$element.append( this.$grid );
};
OO.inheritClass( enhancedUpload.ui.panel.WarningList, OO.ui.HorizontalLayout );

enhancedUpload.ui.panel.WarningList.prototype.addGrid = function ( data ) {
	let gridCfg = {}, i, error, filename;
	this.parseMessages( data ).then( ( result ) => {
		if ( !result ) {
			for ( i = 0; i < data.length; i++ ) {
				filename = data[ i ][ 1 ].name;
				error = data[ i ][ 0 ];
				this.data.push( {
					name: filename,
					error: error
				} );
			}
		} else {
			for ( i = 0; i < data.length; i++ ) {
				filename = data[ i ][ 1 ].name;
				error = result[ i ];
				this.data.push( {
					name: filename,
					error: error
				} );
			}
		}

		gridCfg = {
			pageSize: 10,
			border: 'horizontal',
			toolbar: false,
			columns: {
				name: {
					headerText: mw.message( 'enhancedupload-list-header-filename' ).plain(),
					type: 'text'
				},
				error: {
					headerText: mw.message( 'enhancedupload-list-header-warning' ).plain(),
					type: 'text',
					valueParser: function ( value ) {
						return new OO.ui.HtmlSnippet( value );
					}
				}
			},
			data: this.data
		};

		const voGrid = new OOJSPlus.ui.data.GridWidget( gridCfg );
		this.$grid.html( voGrid.$element );
		this.emit( 'dataset' );
	} );
};

enhancedUpload.ui.panel.WarningList.prototype.parseMessages = function ( data ) {
	const dfd = $.Deferred(),
		resultsDfd = [],
		results = [];

	for ( let i = 0; i < data.length; i++ ) {
		const errorText = data[ i ][ 0 ];
		const parseDfd = this.parse( errorText );
		parseDfd.done( ( parsedText ) => {
			results.push( parsedText );
		} )
			.fail( () => {
				dfd.reject();
			} );
		resultsDfd.push( parseDfd );
	}

	$.when.apply( $, resultsDfd ).done( () => {
		dfd.resolve( results );
	} );
	return dfd.promise();
};

enhancedUpload.ui.panel.WarningList.prototype.parse = function ( text ) {
	const dfd = $.Deferred(),
		api = new mw.Api();

	api.parse( text ).done( ( data ) => {
		dfd.resolve( data );
	} )
		.fail( () => {
			dfd.reject();
		} );

	return dfd.promise();
};
