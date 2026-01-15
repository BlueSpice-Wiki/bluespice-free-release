'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.booklet = ext.pdfcreator.ui.booklet || {};
ext.pdfcreator.ui.booklet.pages = ext.pdfcreator.ui.booklet.pages || {};

ext.pdfcreator.ui.booklet.pages.Intro = function ( name, cfg ) {
	ext.pdfcreator.ui.booklet.pages.Intro.parent.call( this, name, cfg );
	this.toggleUploadPanel( this.useDefaultBg.isSelected() );
};

OO.inheritClass( ext.pdfcreator.ui.booklet.pages.Intro,
	ext.pdfcreator.ui.booklet.pages.SectionPage );

ext.pdfcreator.ui.booklet.pages.Intro.prototype.getElements = function () {
	this.useDefaultBg = new OO.ui.CheckboxInputWidget( {
		selected: !this.data.useDefaultBg || false
	} );
	this.useDefaultBg.connect( this, {
		change: 'toggleUploadPanel'
	} );
	this.backgroundImageInput = new OOJSPlus.ui.widget.FileSearchWidget( {
		extensions: [ 'svg', 'png', 'jpg' ],
		value: this.data.coverBackground || ''
	} );

	this.backgroundImageUpload = new OO.ui.ButtonWidget( {
		icon: 'upload',
		label: mw.message( 'pdfcreator-template-edit-dlg-intro-upload-btn-label' ).text(),
		invisibleLabel: true
	} );
	this.backgroundImageUpload.connect( this, {
		click: function () {
			this.emit( 'upload', this.name );
		}
	} );

	this.backgroundImageFileLayout = new OO.ui.HorizontalLayout( {
		items: [
			new OO.ui.FieldLayout( this.backgroundImageInput, {
				label: mw.message( 'pdfcreator-template-edit-dlg-intro-file-select-label' ).text(),
				align: 'top'
			} ),
			this.backgroundImageUpload
		],
		classes: [ 'pdfcreator-edit-upload-options' ]
	} );

	this.coverTitleInput = this.getParamsCombobox( this.data.introTitle );

	this.coverText = new OO.ui.MultilineTextInputWidget( {
		padded: true,
		rows: 7,
		value: this.data.coverText || ''
	} );

	return [
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.LabelWidget( {
					label: mw.message( 'pdfcreator-template-edit-dlg-intro-page-heading' ).text(),
					classes: [ 'pdfcreator-edit-heading' ]
				} ),
				new OO.ui.LabelWidget( {
					label: mw.message( 'pdfcreator-template-edit-dlg-intro-page-desc' ).text(),
					classes: [ 'pdfcreator-edit-desc' ]
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout(
					new OO.ui.Widget( {
						content: [
							new OO.ui.FieldLayout( this.useDefaultBg, {
								label: mw.message( 'pdfcreator-template-edit-dlg-intro-custom-file-select' ).text(),
								align: 'inline'
							} ),
							this.backgroundImageFileLayout
						]
					} ),
					{
						label: mw.message( 'pdfcreator-template-edit-dlg-intro-background-image-label' ).text(),
						align: 'top'
					} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.coverTitleInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-intro-title-label' ).text(),
					align: 'top',
					help: mw.message( 'pdfcreator-template-edit-dlg-text-input-help-label' ).text()
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.coverText, {
					label: mw.message( 'pdfcreator-template-edit-dlg-intro-text-label' ).text(),
					align: 'top'
				} )
			]
		} )
	];
};

ext.pdfcreator.ui.booklet.pages.Intro.prototype.getData = function () {
	const introData = {
		useDefaultBg: !this.useDefaultBg.isSelected(),
		title: this.coverTitleInput.getValue(),
		text: this.coverText.getValue()
	};
	if ( this.useDefaultBg.isSelected() ) {
		introData.backgroundImage = this.backgroundImageInput.getValue();
	}

	return introData;
};

ext.pdfcreator.ui.booklet.pages.Intro.prototype.getOptionsData = function () {
	if ( this.useDefaultBg.isSelected() ) {
		return this.backgroundImageInput.getValue();
	}
	return '';
};

ext.pdfcreator.ui.booklet.pages.Intro.prototype.setUploadedImage = function ( filename ) {
	this.backgroundImageInput.setValue( filename );
};

ext.pdfcreator.ui.booklet.pages.Intro.prototype.toggleUploadPanel = function ( selected ) {
	this.backgroundImageFileLayout.toggle( selected );
};
