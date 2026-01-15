window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui = window.enhancedUpload.ui || {};

enhancedUpload.ui.AttachmentsWidget = function ( cfg ) {
	enhancedUpload.ui.AttachmentsWidget.parent.call( this, cfg );
	this.pageID = cfg.pageID || 0;
	this.filesTitle = cfg.files || [];
	this.counter = cfg.counter || 0;
	this.tagTitle = cfg.title || '';
	this.loading = true;
	this.editRight = cfg.edit || false;
	this.defaultCategories = cfg.defaultCategories || '';
	this.defaultPrefix = cfg.defaultPrefix || '';
	this.defaultDescription = cfg.defaultDescription || '';
	this.skipOptions = cfg.skipOptions || false;
	this.hideVersion = cfg.hideVersion || false;
	this.hideEditor = cfg.hideEditor || false;
	this.showSize = cfg.showSize || false;
	this.showCategories = cfg.showCategories || false;

	this.$element = $( '<div>' );
};

OO.inheritClass( enhancedUpload.ui.AttachmentsWidget, OO.ui.Widget );

enhancedUpload.ui.AttachmentsWidget.prototype.init = function () {
	const $header = $( '<div>' ).addClass( 'attachments-header' ),
		$mainlabel = $( '<h2>' ).html( this.tagTitle );
	$header.append( $mainlabel );

	if ( this.editRight ) {
		this.addUploadWidget();
		const addNewFileInput = new OO.ui.SelectFileWidget( {
			title: mw.message( 'enhancedupload-attachments-add-new-media-button-title' ).plain(),
			button: {
				icon: 'upload',
				label: ''
			},
			buttonOnly: true,
			multiple: true,
			classes: [ 'add-existing-button' ]
		} );
		$( addNewFileInput.$input ).attr( 'multiple', 'true' );

		const addDataBtn = new OO.ui.ButtonWidget( {
			title: mw.message( 'enhancedupload-attachments-add-media-button-title' ).plain(),
			icon: 'add',
			classes: [ 'add-existing-button' ]
		} );
		addDataBtn.connect( this, {
			click: 'addExistingMedia'
		} );
		addNewFileInput.connect( this, {
			change: function () {
				let items = addNewFileInput.getValue();
				if ( !items.length ) {
					items = [ items ];
				}
				this.upload.startQuickUpload( items );
			}
		} );

		const buttonGroup = new OO.ui.ButtonGroupWidget( {
			items: [ addNewFileInput, addDataBtn ]
		} );

		$header.append( buttonGroup.$element );
	}
	this.$element.append( $header );

	this.dataLayout = new OO.ui.PanelLayout( {
		expanded: false
	} );

	this.$element.append( this.dataLayout.$element );
	this.addGrid();

	if ( this.editRight ) {
		// necessary to allow drop event
		this.$element.on( 'dragover', function ( event ) {
			$( this ).addClass( 'drag-file' );
			event.preventDefault();
			event.originalEvent.dataTransfer.dropEffect = 'move';
			event.originalEvent.dataTransfer.effectAllowed = 'move';
		} );
		this.$element.on( 'dragleave', function ( event ) {
			$( this ).removeClass( 'drag-file' );
			event.preventDefault();
			event.originalEvent.dataTransfer.dropEffect = 'none';
			event.originalEvent.dataTransfer.effectAllowed = 'none';
		} );

		this.$element.on( 'drop', ( e ) => {
			const uploadFiles = e.originalEvent.dataTransfer.files;
			this.upload.startQuickUpload( uploadFiles );
			e.preventDefault();
			$( this.$element ).removeClass( 'drag-file' );
		} );
	}
};

enhancedUpload.ui.AttachmentsWidget.prototype.addGrid = function () {
	const me = this;
	let dataLoaded;
	if ( this.filesTitle.length > 0 ) {
		dataLoaded = this.getGridData();
		dataLoaded.done( ( files ) => {
			me.gridCfg = {
				pageSize: 10,
				columns: {
					filename: {
						headerText: mw.message( 'enhancedupload-attachments-tag-grid-file-label' ).plain(),
						type: 'url',
						editable: false,
						urlProperty: 'file_url',
						sortable: true,
						filter: { type: 'string' },
						autoClosePopup: true
					}
				},
				data: files
			};
			me.setupColumns();

			me.grid = new OOJSPlus.ui.data.GridWidget( me.gridCfg );
			me.grid.connect( me, {
				action: function ( action, row ) {
					if ( action === 'details' ) {
						const fileTitle = mw.Title.newFromText( 'File:' + row.filename );
						window.open( fileTitle.getUrl(), '_blank' );
					}
					if ( action === 'remove' ) {
						OO.ui.confirm( mw.message( 'enhancedupload-attachments-confirm-remove', row.filename ).plain() )
							.done( ( confirmed ) => {
								if ( !confirmed ) {
									return;
								}
								me.removeItems( row );
							} );
					}
				}
			} );

			me.dataLayout.$element.append( me.grid.$element );
			me.emit( 'loaded', me.counter );
		} );
	} else {
		this.dataLayout.$element.append( mw.message( 'enhancedupload-attachments-tag-no-attached-files' ).plain() );
		me.emit( 'loaded', me.counter );
	}
};

enhancedUpload.ui.AttachmentsWidget.prototype.setupColumns = function () {
	if ( !this.hideVersion ) {
		this.gridCfg.columns.version = {
			headerText: mw.message( 'enhancedupload-attachments-tag-grid-version-label' ).plain(),
			type: 'text'
		};
	}

	if ( !this.hideEditor ) {
		this.gridCfg.columns.editor = {
			headerText: mw.message( 'enhancedupload-attachments-tag-grid-editor-label' ).plain(),
			type: 'url',
			editable: false,
			urlProperty: 'editor_url'
		};
	}

	if ( this.showSize ) {
		this.gridCfg.columns.size = {
			headerText: mw.message( 'enhancedupload-attachments-tag-grid-size-label' ).plain(),
			type: 'text',
			editable: false
		};
	}
	if ( this.showCategories ) {
		this.gridCfg.columns.categories = {
			headerText: mw.message( 'enhancedupload-attachments-tag-grid-category-label' ).plain(),
			type: 'url',
			editable: false,
			urlProperty: 'category_url',
			limitShownData: true,
			limitValue: 2
		};
	}

	this.gridCfg.columns.details = {
		type: 'action',
		title: mw.message( 'enhancedupload-attachments-tag-grid-details-title' ).plain(),
		actionId: 'details',
		icon: 'infoFilled'
	};

	if ( this.editRight ) {
		this.gridCfg.columns.remove = {
			type: 'action',
			title: mw.message( 'enhancedupload-attachments-tag-grid-remove-title' ).plain(),
			actionId: 'remove',
			icon: 'close'
		};
	}
};

enhancedUpload.ui.AttachmentsWidget.prototype.getGridData = function () {
	const me = this,
		dfd = new $.Deferred();

	mw.loader.using( 'mediawiki.api' ).done( () => {
		const dfds = [];
		const files = [];
		let i, fileTitle, dfdInfo;
		for ( i = 0; i < me.filesTitle.length; i++ ) {
			fileTitle = me.filesTitle[ i ];
			dfdInfo = me.getFileInfo( fileTitle );
			dfdInfo.done( ( pages ) => {
				let categories = [], title, size, time, url, p, user;
				for ( p in pages ) {
					categories = [];
					if ( !pages[ p ].title || !pages[ p ].imageinfo ) {
						continue;
					}
					title = mw.Title.newFromText( pages[ p ].title );
					size = me.calculateSize( pages[ p ].imageinfo[ 0 ].size );
					time = me.getFormattedTime( pages[ p ].imageinfo[ 0 ].timestamp );
					user = pages[ p ].imageinfo[ 0 ].user;
					url = pages[ p ].imageinfo[ 0 ].url;
					if ( pages[ p ].categories ) {
						categories = me.getCategories( pages[ p ].categories );
					}

					files.push( {
						filename: title.getMainText(),
						// eslint-disable-next-line camelcase
						file_url: url,
						size: size,
						version: time,
						categories: categories.map( ( category ) => category.name ),
						// eslint-disable-next-line camelcase
						category_url: categories.map( ( category ) => category.url ),
						editor: user,
						// eslint-disable-next-line camelcase
						editor_url: mw.Title.newFromText( user, 2 ).getUrl()
					} );
				}
			} );
			dfds.push( dfdInfo );
		}
		$.when.apply( me, dfds ).done( () => {
			// Make sure that files have the same order as in wikitext
			const normalize = ( filename ) => filename.replace( /_/g, ' ' ).trim();
			const sortFiles = new Map( me.filesTitle.map( ( name, idx ) => [ normalize( name ), idx ] ) );
			files.sort( ( a, b ) => sortFiles.get( a.filename ) - sortFiles.get( b.filename ) );
			dfd.resolve( files );
		} );
	} );
	return dfd.promise();

};

enhancedUpload.ui.AttachmentsWidget.prototype.getFormattedTime = function ( timestamp ) {
	const dateSetting = mw.user.options.values.date;
	const monthID = timestamp.slice( 5, 7 ) - 1;

	if ( dateSetting === 'ISO 8601' ) {
		return timestamp;
	}
	const date = new Date( timestamp ).toLocaleDateString();
	return this.insertMonth( date, monthID );
};

enhancedUpload.ui.AttachmentsWidget.prototype.insertMonth = function ( date, monthID ) {
	const month = ' ' + mw.language.months.names[ monthID ] + ' ';
	const posStart = date.indexOf( '.' ) + 1;
	const posEnd = date.lastIndexOf( '.' ) + 1;
	date = date.slice( 0, posStart ) + month + date.slice( posEnd );
	return date;
};

enhancedUpload.ui.AttachmentsWidget.prototype.getFileInfo = function ( fileTitle ) {
	const dfd = new $.Deferred(),
		imageInfoApi = new mw.Api(),
		title = mw.Title.newFromText( 'File:' + fileTitle ),
		params = {
			action: 'query',
			format: 'json',
			prop: 'imageinfo|categories',
			iiprop: 'size|timestamp|url|user',
			titles: title.getPrefixedText()
		};

	imageInfoApi.get( params ).done( ( data ) => {
		dfd.resolve( data.query.pages );
	} );

	return dfd.promise();
};

enhancedUpload.ui.AttachmentsWidget.prototype.getCategories = function ( categoriesInfo ) {
	const categories = [];
	let title, category;
	for ( category in categoriesInfo ) {
		title = mw.Title.newFromText( categoriesInfo[ category ].title );
		categories.push( {
			name: title.getMainText(),
			url: title.getUrl()
		} );
	}
	return categories;
};

enhancedUpload.ui.AttachmentsWidget.prototype.calculateSize = function ( bytes ) {
	const units = [ ' b', ' KB', ' MB', ' GB', ' TB', ' PB' ];
	let i = 0;

	if ( bytes > 0 ) {
		for ( i = 0; bytes >= 1024; bytes /= 1024 ) {
			i++;
		}
	}

	return bytes.toFixed( i > 0 ? 1 : 0 ) + units[ i ];
};

enhancedUpload.ui.AttachmentsWidget.prototype.addUploadWidget = function () {
	const $container = $( '<div>' ).addClass( 'enhancedUpload-widget' );
	this.upload = new enhancedUpload.ui.UploadWidget( {
		container: $container,
		hidePreview: true,
		categories: this.defaultCategories,
		prefix: this.defaultPrefix,
		description: this.defaultDescription,
		skipOptions: this.skipOptions
	} );
	this.upload.connect( this, {
		uploadComplete: 'addItems'
	} );

	this.$element.append( $container );
};

enhancedUpload.ui.AttachmentsWidget.prototype.addItems = function ( widget, items, pageNames ) {
	const me = this;

	if ( items.length < 1 ) {
		return;
	}

	mw.loader.using( 'ext.enhancedUpload.attachments.api' ).done( () => {
		const api = new enhancedUpload.api.Api(),
			addFiles = api.addFiles( me.pageID, me.counter, items, pageNames );
		addFiles.done( () => {
			window.location.reload();
		} );
	} );
};

enhancedUpload.ui.AttachmentsWidget.prototype.removeItems = function ( row ) {
	const me = this,
		items = [ row.filename ];

	mw.loader.using( 'ext.enhancedUpload.attachments.api' ).done( () => {
		const api = new enhancedUpload.api.Api(),
			removedFile = api.removeFiles( me.pageID, me.counter, items );
		removedFile.done( () => {
			window.location.reload();
		} );
	} );
};

enhancedUpload.ui.AttachmentsWidget.prototype.addExistingMedia = function () {
	const me = this;
	mw.loader.using( 'ext.enhancedUpload.attachments.addMediaDialog' ).done( function () {
		this.media = new enhancedUpload.ui.dialog.AddExistingMediaDialog( {
			size: 'medium',
			classes: [ 'attachments-add-dialog' ]
		} );

		this.media.on( 'actioncompleted', ( title ) => {
			mw.loader.using( 'ext.enhancedUpload.attachments.api' ).done( () => {
				const api = new enhancedUpload.api.Api();
				let titleText = title.getMainText();

				if ( title.getNamespaceId() !== 6 ) {
					titleText = title.getPrefixedText();
				}

				const addLink = api.addLink( me.pageID, me.counter, [ titleText ] );
				addLink.done( () => {
					window.location.reload();
				} );
			} );
		} );
		this.media.show();
	} );
};
