window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui = window.enhancedUpload.ui || {};
window.enhancedUpload.ui.dialog = window.enhancedUpload.ui.dialog || {};

enhancedUpload.ui.dialog.VEInsertMediaDialog = function ( cfg ) {
	enhancedUpload.ui.dialog.VEInsertMediaDialog.super.call( this, cfg );
};

OO.inheritClass( enhancedUpload.ui.dialog.VEInsertMediaDialog, ve.ui.NodeDialog );

enhancedUpload.ui.dialog.VEInsertMediaDialog.static.name = 'mediaUpload';

enhancedUpload.ui.dialog.VEInsertMediaDialog.static.title = OO.ui.deferMsg( 'enhancedupload-ve-file-dialog-title' );

enhancedUpload.ui.dialog.VEInsertMediaDialog.static.size = 'medium';

/**
 * @inheritdoc
 */
enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.initialize = function () {
	enhancedUpload.ui.dialog.VEInsertMediaDialog.super.prototype.initialize.call( this );
	this.pageName = mw.config.get( 'wgPageName' );

	// Slashes are no valid chars in a filename
	this.pageName = this.pageName.replace( /\//g, '_' );

	this.targetTitle = new OO.ui.TextInputWidget( {
		value: this.pageName + '_' + Date.now()
	} );
	const titleLayout = new OO.ui.FieldLayout( this.targetTitle, {
		label: mw.message( 'enhancedupload-ve-file-dialog-filename-label' ).plain(),
		align: 'top'
	} );

	const categories = mw.config.get( 'wgCategories', [] );
	const insertCategoryEnabled = mw.config.get( 'bsgInsertCategoryUploadPanelIntegration', false );
	this.categoryInput = new OOJSPlus.ui.widget.CategoryMultiSelectWidget( {
		$overlay: this.$overlay,
		selected: insertCategoryEnabled ? categories : []
	} );
	const categoryLayout = new OO.ui.FieldLayout( this.categoryInput, {
		label: mw.message( 'enhancedupload-details-categories-label' ).plain(),
		align: 'top'
	} );

	this.content = new OO.ui.PanelLayout( {
		padded: true,
		expanded: true
	} );
	this.content.$element.append( titleLayout.$element, categoryLayout.$element );

	this.$body.append( this.content.$element );
};

/**
 * @inheritdoc
 */
enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.getSetupProcess = function ( data ) {
	this.file = data.file;
	this.targetTitle.setValue( this.pageName + '_' + Date.now() );
	return enhancedUpload.ui.dialog.VEInsertMediaDialog.super.prototype.getSetupProcess.call(
		this, data
	);
};

/**
 * @inheritdoc
 */
enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.getBodyHeight = function () {
	if ( !this.$errors.hasClass( 'oo-ui-element-hidden' ) ) { // eslint-disable-line no-jquery/no-class-state
		return this.$element.find( '.oo-ui-processDialog-errors' )[ 0 ].scrollHeight;
	}

	return this.$element.find( '.oo-ui-window-body' )[ 0 ].scrollHeight + 10;
};

enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.getActionProcess = function ( action ) {
	if ( action === 'done' ) {
		const doneActionProcess = this.makeDoneProcess();
		doneActionProcess.next( () => {
			this.close( { action: action } );
		} );
		return doneActionProcess;
	}
	return enhancedUpload.ui.dialog.VEInsertMediaDialog.parent.prototype.getActionProcess.call(
		this,
		action
	);
};

enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.showErrors = function ( errors ) {
	enhancedUpload.ui.dialog.VEInsertMediaDialog.parent.prototype.showErrors.call( this, errors );
	this.updateSize();
};

enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.makeDoneProcess = function () {
	const dfd = new $.Deferred();

	const fileType = this.file.type;
	const fileFormat = this.file.name.slice( Math.max( 0, this.file.name.indexOf( '.' ) + 1 ) );
	const fileName = this.targetTitle.getValue() + '.' + fileFormat;
	const selectedCategories = this.categoryInput.getSelectedCategories().map(
		( cat ) => `[[Category:${ cat }]]`
	).join( '\n' );

	let params = {
		filename: fileName,
		format: fileType,
		ignorewarnings: false,
		text: selectedCategories
	};

	params = this.sanitizeFilename( params );
	params = this.preprocessParams( params );

	const dfdUpload = this.doUpload( this.file, params );

	dfdUpload.done( ( resp ) => {
		this.insertMedia( params.filename, resp.upload.imageinfo.url, this.file );
		dfd.resolve.apply( this );
	} ).fail( ( error, result ) => {
		if ( error === 'fileexists-no-change' || error === 'duplicate' || error === 'exists' ) {
			this.handleErrors( error, result, params.filename, this.fragment );
			dfd.resolve.apply( this );
		} else {
			dfd.reject(
				[ new OO.ui.Error( error, { recoverable: true } ) ]
			);
		}
	} );

	return new OO.ui.Process( dfd.promise(), this );
};

enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.doUpload = function ( file, params ) {
	const mwApi = new mw.Api(),
		dfd = new $.Deferred();
	mwApi.upload( file, params ).done( ( resp ) => {
		dfd.resolve( resp );
	} ).fail( ( error, result ) => {
		let warnings = [],
			errorMessage = mw.message( 'enhancedupload-upload-error-unhandled' ).plain();

		if ( result.error !== undefined ) {
			dfd.reject( result.error.info, result );
		}
		if ( result.upload && result.upload.warnings ) {
			warnings = result.upload.warnings;
		}
		if ( 'exists' in warnings || 'exists-normalized' in warnings ) {
			errorMessage = 'exists';
			if ( 'nochange' in warnings ) {
				errorMessage = 'fileexists-no-change';
			}
		} else if ( 'duplicate' in warnings ) {
			errorMessage = 'duplicate';
		} else if ( 'duplicate-archive' in warnings ) {
			errorMessage = mw.message( 'enhancedupload-upload-error-duplicate', params.filename ).plain();
		} else if ( 'badfilename' in warnings ) {
			errorMessage = mw.message( 'enhancedupload-upload-error-badfilename', params.filename ).plain();
		}
		dfd.reject( errorMessage, result );
	} );

	return dfd.promise();
};

enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.insertMedia = function ( fileName, url, file, fragment ) {
	let title;

	if ( file.type.includes( 'image' ) ) {
		if ( !fragment ) {
			fragment = this.getFragment();
		}

		title = mw.Title.newFromText( 'File:' + fileName );

		const config = require( './insertMediaConfig.json' );
		const alignConfig = config.imagesAlignment;
		const typeConfig = config.imagesType;

		let heightConfig = config.imagesHeight || null;
		let widthConfig = config.imagesWidth || null;

		if ( heightConfig ) {
			heightConfig = parseInt( heightConfig, 10 );
		}
		if ( widthConfig ) {
			widthConfig = parseInt( widthConfig, 10 );
		}

		const isDefaultSize = !heightConfig && !widthConfig;

		this.imageModel = ve.dm.MWImageModel.static.newFromImageAttributes(
			{
				src: url,
				href: './' + title.getPrefixedText(),
				width: widthConfig,
				height: heightConfig,
				resource: title.getPrefixedText(),
				mediaType: file.type,
				type: typeConfig,
				align: alignConfig,
				defaultSize: isDefaultSize
			},
			fragment.getDocument()
		);
		this.imageModel.insertImageNode( fragment );
	} else {
		title = mw.Title.newFromText( 'Media:' + fileName );

		const surfaceModel = ve.init.target.getSurface().getModel();
		fragment = surfaceModel.getFragment();

		const annotationTitle = ve.dm.MWInternalLinkAnnotation.static.newFromTitle(
			title, title.getPrefixedText()
		);

		fragment.insertContent( title.getMainText(), true );
		fragment.annotateContent( 'set', 'link/mwInternal', annotationTitle.element );
	}
};

enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.insertExistingMedia = function ( fragment, fileName ) {
	let url = '';

	if ( this.file.type.includes( 'image' ) ) {
		const title = mw.Title.newFromText( 'File:' + fileName );
		const apiParams = {
			action: 'query',
			format: 'json',
			prop: 'imageinfo',
			iiprop: 'url',
			titles: title.getPrefixedText()
		};

		const imageInfoApi = new mw.Api();
		imageInfoApi.get( apiParams ).done( ( data ) => {
			const pages = data.query.pages;
			for ( const p in pages ) {
				url = pages[ p ].imageinfo[ 0 ].url;
			}
			this.insertMedia( fileName, url, this.file, fragment );
		} );
	} else {
		this.insertMedia( fileName, url, this.file );
	}
};

enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.handleErrors = function ( error, results, fileName, fragment ) {
	const dfd = $.Deferred();
	if ( error === 'fileexists-no-change' ) {
		this.insertExistingMedia( fragment, fileName );
		dfd.resolve.apply( this );
	}
	if ( error === 'duplicate' ) {
		const origFileName = results.upload.warnings.duplicate[ 0 ];
		OO.ui.confirm(
			mw.message( 'enhancedupload-ve-dialog-duplicate-confirm' ).plain() )
			.done( ( confirmed ) => {
				if ( confirmed ) {
					this.insertExistingMedia( fragment, origFileName );
					dfd.resolve.apply( this );
				}
			} );
	}
	if ( error === 'exists' ) {
		OO.ui.prompt(
			mw.message( 'enhancedupload-ve-dialog-title-exists' ).plain(),
			{
				textInput: {
					placeholder: Date.now()
				}
			} )
			.done( ( result ) => {
				const fileFormat = this.file.name.slice( Math.max( 0, this.file.name.indexOf( '.' ) + 1 ) ),
					newFileName = result + '.' + fileFormat;
				let params, dfdReUpload;
				if ( result !== null ) {
					params = {
						filename: newFileName,
						format: this.file.type,
						ignorewarnings: true
					};

					params = this.sanitizeFilename( params );
					params = this.preprocessParams( params );
					dfdReUpload = this.doUpload( this.file, params );
					dfdReUpload.done( ( resp ) => {
						this.insertMedia(
							params.filename,
							resp.upload.imageinfo.url,
							this.file,
							fragment
						);
						this.close( { action: action } ); // eslint-disable-line no-undef
						dfd.resolve.apply( this );
					} )
						.fail( ( err, args ) => {
							if ( err === 'fileexists-no-change' || err === 'duplicate' || err === 'exists' ) {
								this.handleErrors( err, args, params.filename, fragment );
								dfd.resolve.apply( this );
							} else {
								dfd.reject(
									[ new OO.ui.Error( err, { recoverable: false } ) ]
								);
							}
						} );
				}
			} );
	}
	return new OO.ui.Process( dfd.promise(), this );
};

enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.sanitizeFilename = function ( params ) {
	// Split namespace prefix and filename
	const fileNameParts = params.filename.split( ':' );

	const partsLength = fileNameParts.length;

	params.prefix = '';
	for ( let i = 0; i < partsLength - 1; i++ ) {
		params.prefix += fileNameParts[ i ] + ':';
	}
	params.filename = fileNameParts[ partsLength - 1 ];

	// Replace invalid characters in filename
	params.filename = params.filename.replace( /\//g, '_' );

	return params;
};

enhancedUpload.ui.dialog.VEInsertMediaDialog.prototype.preprocessParams = function ( params ) {
	const paramsProcessor = { processors: [ new enhancedUpload.ParamsProcessor() ] };
	mw.hook( 'enhancedUpload.makeParamProcessor' ).fire( paramsProcessor );
	this.paramsProcessors = paramsProcessor.processors;

	const item = { name: params.filename };
	const skipOption = true;

	for ( let i = 0; i < this.paramsProcessors.length; i++ ) {
		const processor = this.paramsProcessors[ i ];
		if ( processor instanceof enhancedUpload.ParamsProcessor ) {
			params = processor.getParams( params, item, skipOption );
		}
	}

	return params;
};

/* Registration */
ve.ui.windowFactory.register( enhancedUpload.ui.dialog.VEInsertMediaDialog );
