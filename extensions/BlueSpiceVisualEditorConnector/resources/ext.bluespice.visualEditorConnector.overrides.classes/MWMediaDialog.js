bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.MWMediaDialog = function BsVecUiMWMediaDialog( config ) {
	bs.vec.ui.MWMediaDialog.super.call( this, config );

	this.uploadType = bs.vec.config.get( 'UploadType' );
};

OO.inheritClass( bs.vec.ui.MWMediaDialog, ve.ui.MWMediaDialog );

ve.ui.MWMediaDialog.static.actions.push( {
	action: 'cancelchoose',
	label: OO.ui.deferMsg( 'visualeditor-dialog-media-goback' ),
	flags: [ 'safe', 'back' ],
	modes: [ 'info_file' ]
}, {
	label: OO.ui.deferMsg( 'visualeditor-dialog-action-cancel' ),
	flags: [ 'safe', 'back' ],
	modes: [ 'info_file_edit' ]
}, {
	action: 'link',
	label: OO.ui.deferMsg( 'bs-visualeditorconnector-dialog-media-link' ),
	flags: [ 'primary' ],
	modes: [ 'info_file', 'info_file_edit' ]
},
{
	action: 'metalink',
	label: OO.ui.deferMsg( 'bs-visualeditorconnector-dialog-media-meta-link' ),
	flags: [ 'primary' ],
	modes: [ 'info_file', 'info_file_edit' ]
},
{
	action: 'choose',
	label: OO.ui.deferMsg( 'bs-visualeditorconnector-dialog-media-embed' ),
	flags: [ 'progressive' ],
	modes: [ 'info_file' ]
}, {
	action: 'change',
	label: OO.ui.deferMsg( 'bs-vec-dialog-media-change-file' ),
	flags: [ 'progressive' ],
	modes: [ 'info_file_edit' ]
} );

bs.vec.ui.MWMediaDialog.prototype.initialize = function () {
	this.initComponentPlugins();
	bs.vec.ui.MWMediaDialog.super.prototype.initialize.call( this );
	this.overwriteUploadBooklet();
	this.annotation = null;

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		plugin.initialize();
	}
	this.emit( 'initComplete' );
};

bs.vec.ui.MWMediaDialog.prototype.overwriteUploadBooklet = function () {
	this.setNewUploadBooklet();
	this.reestablishUploadBookletEventWiring();
};

bs.vec.ui.MWMediaDialog.prototype.setNewUploadBooklet = function () {
	switch ( this.uploadType ) {
		case 'simple':
			this.mediaUploadBooklet = new bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple( {
				$overlay: this.$overlay
			} );
			break;
		case 'one-click':
			this.mediaUploadBooklet = new bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick( {
				$overlay: this.$overlay
			} );
			break;
		case 'original':
		default:
			this.mediaUploadBooklet = new bs.vec.ui.ForeignStructuredUpload.BookletLayout( {
				$overlay: this.$overlay
			} );
			break;
	}

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		plugin.setNewUploadBooklet();
	}
};

bs.vec.ui.MWMediaDialog.prototype.reestablishUploadBookletEventWiring = function () {
	this.mediaUploadBooklet.connect( this, {
		set: 'onMediaUploadBookletSet',
		uploadValid: 'onUploadValid',
		infoValid: 'onInfoValid'
	} );

	const uploadTab = this.searchTabs.getTabPanel( 'upload' );
	this.searchTabs.removeTabPanels( [ uploadTab ] );

	this.searchTabs.addTabPanels( [
		new OO.ui.TabPanelLayout( 'upload', {
			label: ve.msg( 'visualeditor-dialog-media-search-tab-upload' ),
			content: [ this.mediaUploadBooklet ]
		} )
	] );
};

bs.vec.ui.MWMediaDialog.prototype.getReadyProcess = function ( data ) {
	return bs.vec.ui.MWMediaDialog.super.prototype.getReadyProcess.call( this, data )
		.next( function () {
			if ( data.hasOwnProperty( 'file' ) ) {
				// If there is file set (like on paste),
				// switch to upload panel
				this.searchTabs.setTabPanel( 'upload' );
				this.sizeWidget.validateDimensions();
			}
		}, this );
};

bs.vec.ui.MWMediaDialog.prototype.initComponentPlugins = function () {
	this.componentPlugins = [];

	const pluginCallbacks = bs.vec.getComponentPlugins(
		bs.vec.components.MEDIA_DIALOG
	);

	for ( let i = 0; i < pluginCallbacks.length; i++ ) {
		const callback = pluginCallbacks[ i ];
		this.componentPlugins.push( callback( this ) );
	}
};

bs.vec.ui.MWMediaDialog.prototype.getSetupProcess = function ( data ) {
	let parentProcess = bs.vec.ui.MWMediaDialog.super.prototype.getSetupProcess.call( this, data );
	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		parentProcess = plugin.getSetupProcess( parentProcess, data );
	}
	return parentProcess;
};

bs.vec.ui.MWMediaDialog.prototype.setFileLinkEditMode = function ( annotation ) {
	annotation = this.convertAnnotation( annotation );
	this.fileAnnotation = annotation;
	if ( annotation.element.attributes.hasOwnProperty( 'imageInfo' ) ) {
		this.chooseImageInfo( annotation.element.attributes.imageInfo );
		this.mode = 'info_file_edit';
		this.actions.setMode( 'info_file_edit' );
	} else if ( annotation.element.attributes.hasOwnProperty( 'normalizedTitle' ) ) {
		this.switchPanels( 'imageInfo' );
		this.getImageInfoRemote( annotation.element.attributes.normalizedTitle ).done( ( response ) => {
			if ( !response.hasOwnProperty( 'query' ) ) {
				return this.switchPanels( 'search' );
			}
			const pages = response.query.pages;
			for ( const pageId in pages ) {
				const page = pages[ pageId ];
				if ( page.title === annotation.element.attributes.normalizedTitle ) {
					const imageInfo = page.imageinfo.length > 0 ? page.imageinfo[ 0 ] : {};
					imageInfo.title = page.title;
					this.chooseImageInfo( imageInfo );
					this.mode = 'info_file_edit';
					this.actions.setMode( 'info_file_edit' );
					return;
				}
			}
			this.switchPanels( 'search' );
		} ).fail( () => {
			this.switchPanels( 'search' );
		} );
	}
};

bs.vec.ui.MWMediaDialog.prototype.convertAnnotation = function ( annotation ) {
	const title = mw.Title.newFromText( annotation.element.attributes.title );
	if ( title.getNamespaceId() === bs.ns.NS_FILE ) {
		return annotation;
	}

	if ( title.getNamespaceId() === bs.ns.NS_MEDIA ) {
		const fileTitle = mw.Title.makeTitle( bs.ns.NS_FILE, title.getMainText() );
		annotation.element.attributes.title = fileTitle.toText();
		annotation.element.attributes.normalizedTitle = ve.dm.MWInternalLinkAnnotation.static.normalizeTitle( fileTitle );
		annotation.element.attributes.lookupTitle = ve.dm.MWInternalLinkAnnotation.static.getLookupTitle( fileTitle );

		return annotation;
	}
	return null;
};

bs.vec.ui.MWMediaDialog.prototype.getImageInfoRemote = function ( pagename ) {
	return new mw.Api().get( {
		action: 'query',
		format: 'json',
		generator: 'search',
		gsrnamespace: 6,
		iiurlheight: 200,
		iiprop: 'dimensions|url|mediatype|extmetadata|timestamp|user',
		prop: 'imageinfo',
		gsrsearch: pagename,
		iiurlwidth: 300,
		gsroffset: 0,
		gsrlimit: 15
	} );
};

bs.vec.ui.MWMediaDialog.prototype.switchPanels = function ( panel, stopSearchRequery ) {
	this.emit( 'beforePanelSwitch', panel );
	switch ( panel ) {
		case 'search':
			this.setSize( 'larger' );
			this.selectedImageInfo = null;
			if ( !stopSearchRequery ) {
				this.search.getQuery().focus().select();
			}
			// Set the edit panel
			this.panels.setItem( this.mediaSearchPanel );
			this.searchTabs.setTabPanel( 'search' );
			this.searchTabs.toggleMenu( true );
			this.actions.setMode( this.imageModel ? 'change' : 'select' );
			// Layout pending items
			this.search.runLayoutQueue();
			break;
		case 'imageInfo':
			bs.vec.ui.MWMediaDialog.parent.prototype.switchPanels.apply( this, [ panel, stopSearchRequery ] );
			if ( this.isLinkable( this.selectedImageInfo ) ) {
				this.actions.setMode( 'info_file' );
			}
			break;
		default:
			bs.vec.ui.MWMediaDialog.parent.prototype.switchPanels.apply( this, [ panel, stopSearchRequery ] );
			if ( panel === 'edit' ) {
				this.sizeWidget.setDisabled( this.imageModel.getType() === 'frame' );
			}
			break;
	}
	this.emit( 'panelSwitch', panel );
	this.currentPanel = panel || 'imageinfo';
};

bs.vec.ui.MWMediaDialog.prototype.uploadPageNameSet = function ( pageName ) {
	let imageInfo;
	if ( pageName === 'insert' ) {
		imageInfo = this.mediaUploadBooklet.upload.getImageInfo();
		this.selectedImageInfo = imageInfo;
		this.confirmSelectedImage();
		if ( this.isLinkable( this.selectedImageInfo ) ) {
			this.switchPanels( 'imageInfo' );
			this.buildMediaInfoPanel( this.selectedImageInfo );
		} else {
			this.switchPanels( 'edit' );
		}
	} else {
		bs.vec.ui.MWMediaDialog.parent.prototype.uploadPageNameSet.call( this, pageName );
	}
};

bs.vec.ui.MWMediaDialog.prototype.isLinkable = function ( imageInfo ) {
	if ( !imageInfo ) {
		return false;
	}
	switch ( imageInfo.mediatype ) {
		case 'ARCHIVE':
		case 'OFFICE':
		case 'TEXT':
			return true;
	}

	return false;
};

bs.vec.ui.MWMediaDialog.prototype.getActionProcess = function ( action ) {
	let process = null;

	if ( action === 'link' ) {
		process = new OO.ui.Process( this.linkFile.bind( this ) );
	} else if ( action === 'metalink' ) {
		process = new OO.ui.Process( this.linkFileMeta.bind( this ) );
	} else if ( action === 'cancel' ) {
		process = new OO.ui.Process( function () {
			this.close( { action: 'cancel' } );
		} );
	} else {
		process = bs.vec.ui.MWMediaDialog.super.prototype.getActionProcess.call( this, action );
	}

	for ( let i = 0; i < this.componentPlugins.length; i++ ) {
		const plugin = this.componentPlugins[ i ];
		plugin.getActionProcess( process, action );
	}

	return process;

};

bs.vec.ui.MWMediaDialog.prototype.linkFile = function () {
	const linkAnnotation = bs.vec.dm.InternalMediaLinkAnnotation.static.newFromImageInfo( this.selectedImageInfo );

	this.doLinkFile( linkAnnotation, 'link' );
};

bs.vec.ui.MWMediaDialog.prototype.linkFileMeta = function () {
	const linkAnnotation = bs.vec.dm.InternalFileLinkAnnotation.static.newFromImageInfo( this.selectedImageInfo );
	this.doLinkFile( linkAnnotation, 'metalink' );
};

bs.vec.ui.MWMediaDialog.prototype.doLinkFile = function ( linkAnnotation, type ) {
	if ( !this.fileAnnotation ) {
		const imageInfoTitle = mw.Title.newFromText(
			this.selectedImageInfo.title || this.selectedImageInfo.canonicaltitle
		);
		this.getFragment()
			.insertContent( imageInfoTitle.getMainText() );
		this.getFragment().annotateContent( 'set', linkAnnotation );
	} else {
		const fragment = this.getFragment().expandLinearSelection( 'annotation', this.fileAnnotation );
		fragment.annotateContent( 'clear', this.fileAnnotation );
		fragment.annotateContent( 'set', linkAnnotation );
	}

	this.close( { action: type } );
};

// Overwrite fitLabel to get the label completely displayed
bs.vec.ui.MWMediaDialog.prototype.fitLabel = function () {
	bs.vec.ui.MWMediaDialog.super.prototype.fitLabel.call( this );

	let primaryWidth;
	let leftWidth;
	let rightWidth;
	const size = this.getSizeProperties();

	if ( typeof size.width !== 'number' ) {
		if ( this.isOpening() ) {
			if ( !this.fitOnOpen ) {
				// Size is relative and the dialog isn't open yet, so wait.
				// FIXME: This should ideally be handled by setup somehow.
				this.manager.lifecycle.opened.done( this.fitLabel.bind( this ) );
				this.fitOnOpen = true;
			}
			return;
		} else {
			return;
		}
	}

	const safeWidth = this.$safeActions.width();
	primaryWidth = this.$primaryActions.width();

	// sometimes primaryActions.width() returns a smaller width than it has
	// to get the title completely displayed the primaryWidth is set manually
	if ( !OO.ui.isMobile() && primaryWidth < 120 ) {
		primaryWidth = 150;
	}
	if ( this.getDir() === 'ltr' ) {
		leftWidth = safeWidth;
		rightWidth = primaryWidth;
	} else {
		leftWidth = primaryWidth;
		rightWidth = safeWidth;
	}

	this.$location.css( { paddingLeft: leftWidth, paddingRight: rightWidth } );

	return this;
};

bs.vec.ui.MWMediaDialog.prototype.setBodyHeight = function ( height ) {
	this.bodySize = height;
};

bs.vec.ui.MWMediaDialog.prototype.getBodyHeight = function () {
	// 600 is default from parent
	return this.bodySize || 600;
};
