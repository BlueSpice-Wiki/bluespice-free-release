window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.panel = window.enhancedUpload.ui.panel || {};

enhancedUpload.ui.panel.FinishedList = function enhancedUploadUiPanelFinishedList( cfg ) {
	enhancedUpload.ui.panel.FinishedList.parent.call( this, cfg );
	this.data = [];

	this.$grid = $( '<div>' );
	this.$element.append( this.$grid );
};

OO.inheritClass( enhancedUpload.ui.panel.FinishedList, OO.ui.HorizontalLayout );

enhancedUpload.ui.panel.FinishedList.prototype.addGrid = function ( data ) {
	let i, linkTitle, type, page;
	for ( i = 0; i < data.length; i++ ) {
		linkTitle = data[ i ][ 0 ];
		type = data[ i ][ 1 ].type;

		page = new mw.Title( linkTitle );

		this.data.push( {
			type: type,
			name: linkTitle,
			// eslint-disable-next-line camelcase
			page_link: page.getUrl()
		} );
	}

	const gridCfg = {
		pageSize: 10,
		border: 'horizontal',
		toolbar: false,
		columns: {
			name: {
				headerText: mw.message( 'enhancedupload-list-header-link' ).plain(),
				type: 'url',
				urlProperty: 'page_link'
			},
			type: {
				headerText: mw.message( 'enhancedupload-list-header-filetype' ).plain(),
				type: 'text'
			}
		},
		data: this.data
	};

	const voGrid = new OOJSPlus.ui.data.GridWidget( gridCfg );
	this.$grid.html( voGrid.$element );
};
