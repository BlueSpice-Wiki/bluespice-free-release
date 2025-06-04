'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.dialog = ext.pdfcreator.ui.dialog || {};

require( './../booklet/EditBooklet.js' );

ext.pdfcreator.ui.dialog.EditDialog = function ( cfg ) {
	cfg = cfg || {};
	cfg.classes = [ 'pdfcreator-edit-dlg' ];
	ext.pdfcreator.ui.dialog.EditDialog.parent.call( this, cfg );
	this.data = cfg.data || {};
	this.params = cfg.params || {};
	this.mode = cfg.mode || 'create';
	this.data.general.mode = this.mode;
	this.sectionPages = {
		header: 'Header',
		footer: 'Footer',
		intro: 'Intro',
		outro: 'Outro'
	};
};

OO.inheritClass( ext.pdfcreator.ui.dialog.EditDialog, OO.ui.ProcessDialog );

ext.pdfcreator.ui.dialog.EditDialog.static.name = 'PDFEditDialog';
ext.pdfcreator.ui.dialog.EditDialog.static.title = mw.message( 'pdfcreator-template-edit-dlg-title-create' ).text();
ext.pdfcreator.ui.dialog.EditDialog.static.size = 'large';
ext.pdfcreator.ui.dialog.EditDialog.static.actions = [
	{
		action: 'next',
		label: mw.message( 'pdfcreator-template-edit-dlg-action-next' ).text(),
		flags: [ 'primary', 'progressive' ],
		modes: [ 'General', 'Header', 'Footer', 'Intro', 'Outro' ]
	},
	{
		action: 'save',
		label: mw.message( 'pdfcreator-template-edit-dlg-action-save' ).text(),
		flags: [ 'primary', 'progressive' ],
		modes: [ 'Selection' ]
	},
	{
		action: 'upload',
		label: mw.message( 'pdfcreator-template-edit-dlg-action-upload' ).text(),
		flags: [ 'primary', 'progressive' ],
		modes: [ 'Upload' ]
	},
	{
		title: mw.message( 'cancel' ).text(),
		flags: [ 'safe', 'close' ],
		modes: [ 'General' ]
	},
	{
		action: 'back',
		label: mw.message( 'pdfcreator-template-edit-dlg-action-back' ).text(),
		flags: [ 'safe', 'back' ],
		modes: [ 'Selection', 'Header', 'Footer', 'Intro', 'Outro', 'Upload' ]
	}
];

ext.pdfcreator.ui.dialog.EditDialog.prototype.initialize = function () {
	ext.pdfcreator.ui.dialog.EditDialog.super.prototype.initialize.apply( this, arguments );
	this.booklet = new ext.pdfcreator.ui.booklet.EditBooklet( {
		expanded: false,
		outlined: false,
		showMenu: false,
		$overlay: this.$overlay,
		// When auto-focus is enabled - for some reason after changing page is being set twice,
		// which is wrong and breaks stuff.
		// It can be fixed by disabling "autoFocus"
		autoFocus: false,
		data: this.data,
		params: this.params
	} );

	this.$body.append( this.booklet.$element );
};

ext.pdfcreator.ui.dialog.EditDialog.prototype.switchPage = function ( name, data ) {
	const page = this.booklet.getPage( name );
	if ( !page ) {
		return;
	}

	this.booklet.setPage( name );
	this.actions.setMode( name );
	this.popPending();

	switch ( name ) {
		case 'General':
			this.actions.setAbilities( { save: false, cancel: true, back: false, next: true } );

			page.connect( this, {
				titleSet: 'onTitleSet'
			} );
			break;
		case 'Selection':
			this.actions.setAbilities( { save: true, cancel: false, back: true, next: false } );
			page.connect( this, {
				configureSection: function ( section ) {
					this.switchPage( this.sectionPages[ section ], {} );
				}
			} );
			break;
		case 'Header':
			this.actions.setAbilities( { save: false, cancel: false, back: true, next: true } );
			page.connect( this, {
				upload: function ( backToPage ) {
					this.switchPage( 'Upload', { backTo: backToPage } );
				}
			} );
			break;
		case 'Footer':
			this.actions.setAbilities( { save: false, cancel: false, back: true, next: true } );
			break;
		case 'Intro':
			this.actions.setAbilities( { save: false, cancel: false, back: true, next: true } );
			page.connect( this, {
				upload: function ( backToPage ) {
					this.switchPage( 'Upload', { backTo: backToPage } );
				}
			} );
			break;
		case 'Outro':
			this.actions.setAbilities( { save: false, cancel: false, back: true, next: true } );
			break;
		case 'Upload':
			page.setBackTo( data.backTo );
			this.actions.setAbilities( {
				save: false, cancel: false, back: true, next: true, upload: false
			} );

			page.connect( this, {
				fileSet: function ( files ) {
					if ( files.length > 0 ) {
						this.actions.setAbilities( { upload: true } );
						return;
					}
					this.actions.setAbilities( { upload: false } );
				}
			} );
			break;
	}
};

ext.pdfcreator.ui.dialog.EditDialog.prototype.onTitleSet = function ( title ) {
	if ( title.length ) {
		this.actions.setAbilities( { next: true } );
	} else {
		this.actions.setAbilities( { next: false } );
	}
};

ext.pdfcreator.ui.dialog.EditDialog.prototype.getReadyProcess = function ( data ) {
	return ext.pdfcreator.ui.dialog.EditDialog.parent.prototype.getReadyProcess.call( this, data )
		.next(
			function () {
				this.switchPage( 'General', {} );
				if ( this.mode !== 'edit' ) {
					this.actions.setAbilities( {
						save: false, cancel: true, back: false, next: false
					} );
				}
			},
			this
		);
};

ext.pdfcreator.ui.dialog.EditDialog.prototype.getSetupProcess = function ( data ) {
	return ext.pdfcreator.ui.dialog.EditDialog.parent.prototype.getSetupProcess.call( this, data )
		.next( function () {
			// Prevent flickering, disable all actions before init is done
			this.actions.setMode( 'INVALID' );
			if ( this.mode === 'edit' ) {
				this.title.setLabel( mw.message( 'pdfcreator-template-edit-dlg-title' ).text() );
			}
		}, this );
};

ext.pdfcreator.ui.dialog.EditDialog.prototype.getActionProcess = function ( action ) {
	return ext.pdfcreator.ui.dialog.EditDialog.parent.prototype.getActionProcess.call(
		this, action )
		.next(
			function () {
				const dfd = $.Deferred();
				if ( action === 'next' ) {
					this.pushPending();

					const page = this.booklet.getCurrentPage();

					if ( page.name === 'General' ) {
						this.data.general = this.booklet.getCurrentPage().getData();

						page.checkTitle( this.data.general.title ).done( () => {
							this.switchPage( 'Selection' );
							dfd.resolve();
						} ).fail( ( error, xhr ) => {
							this.popPending();
							if ( error === 'exists' ) {
								this.actions.setAbilities( { next: false } );
								dfd.resolve();
							} else {
								const errorObj = new OO.ui.Error(
									xhr.responseJSON.message,
									{ recoverable: false }
								);
								dfd.reject( errorObj );
							}
						} );
					}
					if ( page.name === 'Header' ) {
						this.data.header = this.booklet.getCurrentPage().getData();
						if ( Object.keys( this.data.header ).length > 0 ) {
							const selectionPage = this.booklet.getPage( 'Selection' );
							selectionPage.setSelected( 'header', true );
						}
						this.switchPage( 'Selection' );
						dfd.resolve();
					}
					if ( page.name === 'Footer' ) {
						this.data.footer = this.booklet.getCurrentPage().getData();
						if ( Object.keys( this.data.footer ).length > 0 ) {
							const selectionPage = this.booklet.getPage( 'Selection' );
							selectionPage.setSelected( 'footer', true );
						}
						this.switchPage( 'Selection' );
						dfd.resolve();
					}
					if ( page.name === 'Intro' ) {
						this.data.intro = this.booklet.getCurrentPage().getData();
						if ( Object.keys( this.data.intro ).length > 0 ) {
							const selectionPage = this.booklet.getPage( 'Selection' );
							selectionPage.setSelected( 'intro', true );
						}
						this.switchPage( 'Selection' );
						dfd.resolve();
					}
					if ( page.name === 'Outro' ) {
						this.data.outro = this.booklet.getCurrentPage().getData();
						if ( Object.keys( this.data.outro ).length > 0 ) {
							const selectionPage = this.booklet.getPage( 'Selection' );
							selectionPage.setSelected( 'outro', true );
						}
						this.switchPage( 'Selection' );
						dfd.resolve();
					}
					this.popPending();
					return dfd.promise();
				} else if ( action === 'save' ) {
					const dfdSave = $.Deferred();
					this.pushPending();
					const selectedSections = this.booklet.getCurrentPage().getData();
					this.data = {};
					for ( const section in selectedSections ) {
						this.data[ section ] = this.booklet.getPage(
							this.sectionPages[ section ] ).getData();
					}
					this.data.general = this.booklet.getPage( 'General' ).getData();
					if ( selectedSections.intro ) {
						this.data.general.options.coverBackground = this.booklet.getPage( 'Intro' ).getOptionsData();
					}
					this.save().done( () => {
						this.close();
						this.emit( 'saved' );
						dfdSave.resolve();
					} ).fail( ( error ) => {
						this.showErrors( [ error ] );
						this.popPending();
						dfdSave.reject();
					} );
					return dfdSave.promise();
				} else if ( action === 'done' ) {
					return this.close();
				} else if ( action === 'back' ) {
					const page = this.booklet.getCurrentPage();
					if ( page.name === 'Selection' ) {
						this.switchPage( 'General' );
					} else if ( page.name === 'Upload' ) {
						this.switchPage( page.getBackToPage() );
					} else {
						this.switchPage( 'Selection' );
					}
					return dfd.promise();
				} else if ( action === 'upload' ) {
					this.pushPending();
					const page = this.booklet.getCurrentPage();
					const file = page.getFile();
					this.doUpload( file ).done( ( filename ) => {
						this.popPending();
						const backToPage = page.getBackToPage();
						const switchPage = this.booklet.getPage( backToPage );
						switchPage.setUploadedImage( filename );
						this.switchPage( backToPage );
						dfd.resolve();
					} ).fail( ( errorMsg ) => {
						this.showErrors( new OO.ui.Error( errorMsg, { recoverable: false } ) );
						dfd.reject();
						this.popPending();
					} );
					return dfd.promise();
				}

				return ext.pdfcreator.ui.dialog.EditDialog.parent.prototype.getActionProcess.call(
					this,
					action
				);
			},
			this
		);
};

ext.pdfcreator.ui.dialog.EditDialog.prototype.save = function () {
	const dfd = $.Deferred();
	mw.loader.using( [ 'ext.pdfcreator.export.api' ] ).done( () => {
		const api = new ext.pdfcreator.api.Api();
		api.saveEdit( this.data.general.title, this.data ).done( () => {
			dfd.resolve();
		} ).fail( ( error ) => {
			dfd.reject( new OO.ui.Error( error, { recoverable: false } ) );
		} );
	} );

	return dfd.promise();
};

ext.pdfcreator.ui.dialog.EditDialog.prototype.doUpload = function ( file ) {
	const dfd = $.Deferred();
	mw.loader.using( [ 'mediawiki.api' ] ).done( () => {
		const mwApi = new mw.Api();
		const params = {
			filename: file.name,
			format: file.type,
			ignorewarnings: false
		};

		mwApi.upload( file, params ).done( () => {
			dfd.resolve( file.name );
		} ).fail( ( error, result ) => {
			const errorMessage = this.getErrorMsg( result );
			dfd.reject( errorMessage, result );
		} );
	} );

	return dfd.promise();
};

ext.pdfcreator.ui.dialog.EditDialog.prototype.getErrorMsg = function ( result ) {
	// eslint-disable-next-line no-prototype-builtins
	if ( !result.hasOwnProperty( 'upload' ) ) {
		return 'No upload property during upload';
	}
	const upload = result.upload;
	// eslint-disable-next-line no-prototype-builtins
	if ( !upload.hasOwnProperty( 'warnings' ) ) {
		return 'No warnings during upload';
	}
	const warnings = result.upload.warnings;
	let errorMessage = mw.message( 'pdfcreator-template-edit-dlg-upload-error-unhandled' ).plain();
	if ( 'exists' in warnings || 'exists-normalized' in warnings ) {
		errorMessage = 'exists';
		if ( 'nochange' in warnings ) {
			errorMessage = 'fileexists-no-change';
		}
	} else if ( 'duplicate' in warnings ) {
		errorMessage = 'duplicate';
	} else if ( 'duplicate-archive' in warnings ) {
		errorMessage = mw.message( 'pdfcreator-template-edit-dlg-upload-error-duplicate', upload.filename ).plain();
	}
	return errorMessage;
};
