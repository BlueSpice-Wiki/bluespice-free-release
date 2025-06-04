'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.dialog = ext.pdfcreator.ui.dialog || {};

ext.pdfcreator.ui.dialog.DuplicateDialog = function ( cfg ) {
	cfg = cfg || {};
	cfg.classes = [ 'pdfcreator-duplicate-dlg' ];
	ext.pdfcreator.ui.dialog.DuplicateDialog.super.call( this, cfg );
	this.templatePagePrefix = 'MediaWiki:PDFCreator/';
	this.originTemplate = cfg.originTemplate || '';
};

OO.inheritClass( ext.pdfcreator.ui.dialog.DuplicateDialog, OO.ui.ProcessDialog );

ext.pdfcreator.ui.dialog.DuplicateDialog.static.name = 'PDFDuplicateDialog';
ext.pdfcreator.ui.dialog.DuplicateDialog.static.title = mw.message( 'pdfcreator-template-duplicate-dlg-title' ).text();
ext.pdfcreator.ui.dialog.DuplicateDialog.static.size = 'medium';
ext.pdfcreator.ui.dialog.DuplicateDialog.static.actions = [
	{
		action: 'duplicate',
		label: mw.message( 'pdfcreator-template-duplicate-dlg-action-copy' ).text(),
		flags: [ 'primary', 'progressive' ]
	},
	{
		label: mw.message( 'cancel' ).text(),
		flags: [ 'safe', 'close' ]
	}
];

ext.pdfcreator.ui.dialog.DuplicateDialog.prototype.initialize = function () {
	ext.pdfcreator.ui.dialog.DuplicateDialog.super.prototype.initialize.apply( this, arguments );
	this.panel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	this.titleInput = new OO.ui.TextInputWidget( {
		padded: true,
		required: true,
		value: this.originTemplate + ' (2)'
	} );

	this.titleInput.connect( this, {
		change: 'onTitleSet'
	} );

	this.titleInputField = new OO.ui.FieldLayout( this.titleInput, {
		label: mw.message( 'pdfcreator-template-duplicate-dlg-new-title' ).text(),
		align: 'top'
	} );

	this.panel.$element.append( this.titleInputField.$element );

	this.$body.append( this.panel.$element );
	this.updateSize();
};

ext.pdfcreator.ui.dialog.DuplicateDialog.prototype.getActionProcess = function ( action ) {
	return ext.pdfcreator.ui.dialog.DuplicateDialog.parent.prototype.getActionProcess.call(
		this, action )
		.next(
			function () {
				this.pushPending();
				const dfd = $.Deferred();
				if ( action === 'duplicate' ) {
					this.checkTitle().done( () => {
						this.duplicate().done( () => {
							this.close();
							this.emit( 'copied' );
						} ).fail( ( error ) => {
							this.popPending();
							this.showErrors( error );
						} );
					} ).fail( () => {
						this.popPending();
						this.actions.setAbilities( { duplicate: false } );
					} );
					return dfd.promise();
				}

				return ext.pdfcreator.ui.dialog.DuplicateDialog.parent.prototype.getActionProcess
					.call( this, action );
			},
			this
		);
};

ext.pdfcreator.ui.dialog.DuplicateDialog.prototype.duplicate = function () {
	const dfd = new $.Deferred();
	const contentDfd = this.getContent();
	contentDfd.done( ( data ) => {
		mw.loader.using( [ 'ext.pdfcreator.export.api' ] ).done( () => {
			const api = new ext.pdfcreator.api.Api();
			api.save( this.titleInput.getValue(), data )
				.done( () => {
					dfd.resolve();
				} ).fail( () => {
					dfd.reject( [ new OO.ui.Error( arguments[ 0 ], { recoverable: false } ) ] );
				} );
		} );
	} ).fail( ( error ) => {
		dfd.reject( error );
	} );
	return dfd.promise();
};

ext.pdfcreator.ui.dialog.DuplicateDialog.prototype.getContent = function () {
	const dfd = new $.Deferred();
	mw.loader.using( [ 'mediawiki.api' ] ).done( () => {
		const mwApi = new mw.Api();
		mwApi.postWithToken( 'csrf', {
			action: 'query',
			titles: this.templatePagePrefix + this.originTemplate,
			prop: 'revisions',
			rvprop: 'content',
			rvslots: '*',
			indexpageids: ''
		} ).done( ( resp ) => {
			const pageId = resp.query.pageids[ 0 ];
			const pageInfo = resp.query.pages[ pageId ];

			if ( pageInfo.missing || !pageInfo.revisions ||
				!pageInfo.revisions[ 0 ] || !pageInfo.revisions[ 0 ].slots ) {
				dfd.reject( resp );
			}
			const data = {};
			for ( const slot in pageInfo.revisions[ 0 ].slots ) {
				data[ slot ] = pageInfo.revisions[ 0 ].slots[ slot ][ '*' ];
			}
			dfd.resolve( data );
		} ).fail( () => {
			dfd.reject( [ new OO.ui.Error( arguments[ 0 ], { recoverable: false } ) ] );
		} );
	} );
	return dfd.promise();
};

ext.pdfcreator.ui.dialog.DuplicateDialog.prototype.onTitleSet = function ( title ) {
	this.titleInputField.setErrors( [] );
	if ( title.length ) {
		this.actions.setAbilities( { duplicate: true } );
	} else {
		this.actions.setAbilities( { duplicate: false } );
	}
};

ext.pdfcreator.ui.dialog.DuplicateDialog.prototype.checkTitle = function () {
	const deferred = $.Deferred();
	mw.loader.using( [ 'mediawiki.api' ] ).done( () => {
		const api = new mw.Api();
		const queryData = {
			action: 'query',
			titles: this.templatePagePrefix + this.titleInput.getValue(),
			prop: 'info'
		};
		api.get( queryData ).done( ( response ) => {
			if ( response.query.pages ) {
				const pageId = Object.keys( response.query.pages )[ 0 ];
				if ( pageId < 0 ) {
					deferred.resolve();
				} else {
					this.titleInput.setValidityFlag( false );
					this.titleInputField.setErrors( [ mw.message( 'pdfcreator-template-dlg-template-name-error-label' ).text() ] );
					deferred.reject( 'exists' );
				}
			}
		} ).fail( ( error ) => {
			deferred.reject( error );
		} );
	} );
	return deferred.promise();
};
