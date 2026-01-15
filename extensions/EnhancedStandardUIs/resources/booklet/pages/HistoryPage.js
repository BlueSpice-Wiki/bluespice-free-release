ext.enhancedUI.booklet.HistoryPage = function ( name, cfg ) {
	this.fileName = cfg.fileData.dbkey;
	ext.enhancedUI.booklet.HistoryPage.parent.call( this, name, cfg );

	this.versions = [];
	this.getElements().done( () => {
		this.gridCfg = {
			pageSize: 10,
			columns: {
				version: {
					headerText: mw.message( 'enhanced-standard-uis-filelist-dialog-history-grid-version' ).text(),
					type: 'url',
					urlProperty: 'url'
				},
				user: {
					headerText: mw.message( 'enhanced-standard-uis-filelist-grid-author-title' ).text(),
					type: 'user'
				},
				size: {
					headerText: mw.message( 'enhanced-standard-uis-filelist-grid-size-title' ).text(),
					type: 'number'
				},
				comment: {
					headerText: mw.message( 'enhanced-standard-uis-filelist-grid-comment-title' ).text(),
					type: 'text'
				}
			},
			data: this.versions
		};

		this.grid = new OOJSPlus.ui.data.GridWidget( this.gridCfg );
		this.$element.append( this.grid.$element );
		this.emit( 'update' );
	} );
};

OO.inheritClass( ext.enhancedUI.booklet.HistoryPage, OO.ui.PageLayout );

ext.enhancedUI.booklet.HistoryPage.prototype.setupOutlineItem = function () {
	this.outlineItem.setLabel(
		mw.message( 'enhanced-standard-uis-filelist-dialog-page-history-label' ).text()
	);
};

ext.enhancedUI.booklet.HistoryPage.prototype.getElements = function () {
	const dfd = $.Deferred();
	const title = mw.Title.newFromText( 'File:' + this.fileName ),
		imageInfoApi = new mw.Api(),
		apiParams = {
			action: 'query',
			format: 'json',
			prop: 'imageinfo',
			iilimit: 'max',
			iiprop: 'timestamp|user|url|comment|size',
			titles: title.getPrefixedText()
		};

	imageInfoApi.get( apiParams ).done( ( data ) => {
		const pages = data.query.pages;
		let p;
		for ( p in pages ) {
			for ( const v in pages[ p ].imageinfo ) {
				const timestamp = this.convertTimestamp( pages[ p ].imageinfo[ v ].timestamp );
				const size = this.calculateSize( pages[ p ].imageinfo[ v ].size );
				const version = {
					version: timestamp,
					url: pages[ p ].imageinfo[ v ].url,
					user: pages[ p ].imageinfo[ v ].user,
					size: size,
					comment: pages[ p ].imageinfo[ v ].comment
				};
				this.versions.push( version );
			}
		}

		dfd.resolve();
	} ).fail( ( error ) => {
		dfd.reject( new OO.ui.Error( error ) );
	} );

	return dfd.promise();
};

ext.enhancedUI.booklet.HistoryPage.prototype.convertTimestamp = function ( timestamp ) {
	const dateSetting = mw.user.options.values.date;
	const monthID = timestamp.slice( 5, 7 ) - 1;

	if ( dateSetting === 'ISO 8601' ) {
		return timestamp;
	}
	const date = new Date( timestamp ).toLocaleDateString();
	return this.insertMonth( date, monthID );
};

ext.enhancedUI.booklet.HistoryPage.prototype.insertMonth = function ( date, monthID ) {
	const month = ' ' + mw.language.months.names[ monthID ] + ' ';
	const posStart = date.indexOf( '.' ) + 1;
	const posEnd = date.lastIndexOf( '.' ) + 1;
	date = date.slice( 0, posStart ) + month + date.slice( posEnd );
	return date;
};

ext.enhancedUI.booklet.HistoryPage.prototype.calculateSize = function ( bytes ) {
	let i = 0;
	const units = [ ' b', ' KB', ' MB', ' GB', ' TB', ' PB' ];

	if ( bytes > 0 ) {
		for ( i = 0; bytes >= 1024; bytes /= 1024 ) {
			i++;
		}
	}

	return bytes.toFixed( i > 0 ? 1 : 0 ) + units[ i ];
};
