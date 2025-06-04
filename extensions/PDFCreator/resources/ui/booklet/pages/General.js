'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.booklet = ext.pdfcreator.ui.booklet || {};
ext.pdfcreator.ui.booklet.pages = ext.pdfcreator.ui.booklet.pages || {};

ext.pdfcreator.ui.booklet.pages.General = function ( name, cfg ) {
	ext.pdfcreator.ui.booklet.pages.General.parent.call( this, name, cfg );
};

OO.inheritClass( ext.pdfcreator.ui.booklet.pages.General,
	ext.pdfcreator.ui.booklet.pages.SectionPage );

ext.pdfcreator.ui.booklet.pages.General.prototype.getElements = function () {
	this.titleInput = new OO.ui.TextInputWidget( {
		padded: true,
		required: true
	} );
	if ( this.data.mode === 'edit' ) {
		this.titleInput.setValue( this.data.template );
		this.titleInput.setDisabled( true );
	}

	this.titleInput.connect( this, {
		change: function ( value ) {
			this.titleInputField.setErrors( [] );
			this.emit( 'titleSet', value );
		}
	} );

	this.titleInputField = new OO.ui.FieldLayout( this.titleInput, {
		label: mw.message( 'pdfcreator-template-edit-dlg-general-template-input-label' ).text(),
		align: 'top'
	} );

	this.sizeButtonsInput = new OO.ui.ButtonSelectWidget( {
		items: [
			new OO.ui.ButtonOptionWidget( {
				data: 'portrait',
				label: mw.message( 'pdfcreator-template-edit-dlg-general-size-btn-portrait-label' ).text(),
				selected: this.data.size === 'portrait'
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'landscape',
				label: mw.message( 'pdfcreator-template-edit-dlg-general-size-btn-landscape-label' ).text(),
				selected: this.data.size === 'landscape'
			} )
		]
	} );

	this.nsPrefixInput = new OO.ui.CheckboxInputWidget( {
		selected: this.data.options.nsPrefix ?? true
	} );
	this.TOCSelectInput = new OO.ui.CheckboxInputWidget( {
		selected: this.data.options[ 'embed-page-toc' ] ?? true
	} );
	this.noRedirect = new OO.ui.CheckboxInputWidget( {
		selected: this.data.options[ 'no-redirect' ] ?? false
	} );
	this.attachmentInput = new OO.ui.CheckboxInputWidget( {
		selected: this.data.options.attachments ?? true
	} );
	this.disableLinksInput = new OO.ui.CheckboxInputWidget( {
		selected: this.data.options[ 'suppress-links' ] ?? false
	} );

	this.descInput = new OO.ui.MultilineTextInputWidget( {
		padded: true,
		value: this.data.desc
	} );

	let headingMsg = mw.message( 'pdfcreator-template-edit-dlg-general-page-heading' ).text();
	if ( this.data.mode === 'edit' ) {
		headingMsg = mw.message( 'pdfcreator-template-edit-dlg-general-page-heading-edit' ).text();
	}

	return [
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.LabelWidget( {
					label: headingMsg,
					classes: [ 'pdfcreator-edit-heading' ]
				} ),
				new OO.ui.LabelWidget( {
					label: mw.message( 'pdfcreator-template-edit-dlg-general-page-desc' ).text(),
					classes: [ 'pdfcreator-edit-desc' ]
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				this.titleInputField
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.sizeButtonsInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-general-size-label' ).text(),
					align: 'top'
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			classes: [ 'pdfcreator-general-properties' ],
			label: mw.message( 'pdfcreator-template-edit-dlg-general-properties-label' ).text(),
			items: [
				new OO.ui.FieldLayout( this.nsPrefixInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-general-ns-prefix-label' ).text(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.TOCSelectInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-general-toc-label' ).text(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.noRedirect, {
					label: mw.message( 'pdfcreator-template-edit-dlg-general-redirect-label' ).text(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.attachmentInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-general-attachments-label' ).text(),
					align: 'inline'
				} ),
				new OO.ui.FieldLayout( this.disableLinksInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-general-links-label' ).text(),
					align: 'inline'
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.descInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-template-desc-label' ).text(),
					align: 'top'
				} )
			]
		} )
	];
};

ext.pdfcreator.ui.booklet.pages.General.prototype.getData = function () {
	return {
		title: this.titleInput.getValue(),
		size: this.sizeButtonsInput.findSelectedItem().data,
		options: {
			'embed-page-toc': this.TOCSelectInput.isSelected(),
			nsPrefix: this.nsPrefixInput.isSelected(),
			attachments: this.attachmentInput.isSelected(),
			'suppress-links': this.disableLinksInput.isSelected(),
			'no-redirect': this.noRedirect.isSelected()
		},
		desc: this.descInput.getValue()
	};
};

ext.pdfcreator.ui.booklet.pages.General.prototype.checkTitle = function ( title ) {
	const deferred = $.Deferred();
	if ( this.data.mode === 'edit' ) {
		deferred.resolve();
		return deferred.promise();
	}
	mw.loader.using( [ 'mediawiki.api' ] ).done( () => {
		const api = new mw.Api();
		const queryData = {
			action: 'query',
			titles: 'MediaWiki:PDFCreator/' + title,
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
