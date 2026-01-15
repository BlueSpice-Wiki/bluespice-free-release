window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.panel = window.enhancedUpload.ui.panel || {};

enhancedUpload.ui.panel.FailedList = function enhancedUploadUiPanelFailedList( cfg ) {
	enhancedUpload.ui.panel.FailedList.parent.call( this, cfg );
	this.data = [];

	this.$grid = $( '<div>' );
	this.$element.append( this.$grid );
};
OO.inheritClass( enhancedUpload.ui.panel.FailedList, OO.ui.HorizontalLayout );

enhancedUpload.ui.panel.FailedList.prototype.addGrid = function ( data ) {
	let i, errorType, filename, type;
	for ( i = 0; i < data.length; i++ ) {
		errorType = data[ i ][ 0 ];
		type = data[ i ][ 1 ].type;
		filename = data[ i ][ 1 ].name;

		this.data.push( {
			typ: type,
			name: filename,
			error: errorType
		} );
	}

	const gridCfg = {
		pageSize: 10,
		border: 'horizontal',
		toolbar: false,
		columns: {
			typ: {
				headerText: mw.message( 'enhancedupload-list-header-filetype' ).plain(),
				type: 'text'
			},
			name: {
				headerText: mw.message( 'enhancedupload-list-header-filename' ).plain(),
				type: 'text'
			},
			error: {
				headerText: mw.message( 'enhancedupload-list-header-error' ).plain(),
				type: 'text'
			}
		},
		data: this.data
	};

	const voGrid = new OOJSPlus.ui.data.GridWidget( gridCfg );
	this.$grid.html( voGrid.$element );
};
