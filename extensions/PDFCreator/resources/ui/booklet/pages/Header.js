'use strict';

window.ext = window.ext || {};

ext.pdfcreator = ext.pdfcreator || {};
ext.pdfcreator.ui = ext.pdfcreator.ui || {};
ext.pdfcreator.ui.booklet = ext.pdfcreator.ui.booklet || {};
ext.pdfcreator.ui.booklet.pages = ext.pdfcreator.ui.booklet.pages || {};

ext.pdfcreator.ui.booklet.pages.Header = function ( name, cfg ) {
	ext.pdfcreator.ui.booklet.pages.Header.parent.call( this, name, cfg );

	this.toggleUploadPanel( this.useWikiLogo.isSelected() );
};

OO.inheritClass( ext.pdfcreator.ui.booklet.pages.Header,
	ext.pdfcreator.ui.booklet.pages.Footer );

ext.pdfcreator.ui.booklet.pages.Header.prototype.getElements = function () {
	this.useWikiLogo = new OO.ui.CheckboxInputWidget( {
		selected: !this.data.useWikiLogo || false
	} );
	this.useWikiLogo.connect( this, {
		change: 'toggleUploadPanel'
	} );

	this.headerImage = new OOJSPlus.ui.widget.FileSearchWidget( {
		extensions: [ 'svg', 'png', 'jpg' ],
		value: this.data.logoName || ''
	} );
	this.headerImage.connect( this, {
		change: 'updateImage'
	} );

	this.headerImageUpload = new OO.ui.ButtonWidget( {
		icon: 'upload',
		label: mw.message( 'pdfcreator-template-edit-dlg-header-upload-btn-label' ).text(),
		invisibleLabel: true
	} );
	this.headerImageUpload.connect( this, {
		click: function () {
			this.emit( 'upload', this.name );
		}
	} );

	this.headerText = this.getParamsCombobox( this.data.headerText );
	this.headerText.connect( this, {
		change: function () {
			this.updatePreview();
		}
	} );

	this.headerImageFileLayout = new OO.ui.HorizontalLayout( {
		items: [
			new OO.ui.FieldLayout( this.headerImage, {
				label: mw.message( 'pdfcreator-template-edit-dlg-header-file-select-label' ).text(),
				align: 'top'
			} ),
			this.headerImageUpload
		],
		classes: [ 'pdfcreator-edit-upload-options' ]
	} );

	const isLeftAligned = this.data.leftAlign === true;
	this.headerLayoutInput = new OO.ui.ButtonSelectWidget( {
		items: [
			new OO.ui.ButtonOptionWidget( {
				data: 'left',
				label: mw.message( 'pdfcreator-template-edit-dlg-header-btn-left-align-label' ).text(),
				selected: isLeftAligned
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'right',
				label: mw.message( 'pdfcreator-template-edit-dlg-header-btn-right-align-label' ).text(),
				selected: !isLeftAligned
			} )
		]
	} );
	this.headerLayoutInput.connect( this, {
		select: function () {
			this.updatePreview();
		}
	} );

	return [
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.LabelWidget( {
					label: mw.message( 'pdfcreator-template-edit-dlg-header-page-heading' ).text(),
					classes: [ 'pdfcreator-edit-heading' ]
				} ),
				new OO.ui.LabelWidget( {
					label: mw.message( 'pdfcreator-template-edit-dlg-header-page-desc' ).text(),
					classes: [ 'pdfcreator-edit-desc' ]
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout(
					new OO.ui.Widget( {
						content: [
							new OO.ui.FieldLayout( this.useWikiLogo, {
								label: mw.message( 'pdfcreator-template-edit-dlg-header-custom-file-select' ).text(),
								align: 'inline'
							} ),
							this.headerImageFileLayout
						]
					} ),
					{
						label: mw.message( 'pdfcreator-template-edit-dlg-header-image-label' ).text(),
						align: 'top'
					} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.headerText, {
					label: mw.message( 'pdfcreator-template-edit-dlg-header-text-label' ).text(),
					align: 'top',
					help: mw.message( 'pdfcreator-template-edit-dlg-text-input-help-label' ).text()
				} )
			]
		} ),
		new OO.ui.FieldsetLayout( {
			items: [
				new OO.ui.FieldLayout( this.headerLayoutInput, {
					label: mw.message( 'pdfcreator-template-edit-dlg-header-layout-label' ).text(),
					align: 'top'
				} )
			]
		} )
	];
};

ext.pdfcreator.ui.booklet.pages.Header.prototype.getData = function () {
	const headerData = {
		useWikiLogo: !this.useWikiLogo.isSelected(),
		headerText: this.headerText.getValue()
	};

	if ( this.useWikiLogo.isSelected() ) {
		headerData.headerImage = this.headerImage.getValue();
	}

	const selectedLayout = this.headerLayoutInput.findSelectedItem();
	let imageLeft = true;
	if ( selectedLayout.data !== 'left' ) {
		imageLeft = false;
	}

	headerData.leftAlign = imageLeft;

	return headerData;
};

ext.pdfcreator.ui.booklet.pages.Header.prototype.appendPreview = function () {
	const $previewCnt = $( '<div>' ).addClass( 'pdfcreator-header-preview-cnt' );
	const $hintText = $( '<span>' ).addClass( 'pdfcreator-header-preview-text' ).text(
		mw.message( 'pdfcreator-template-edit-dlg-header-preview-text' ).text() );
	$previewCnt.append( $hintText );

	this.$headerImage = $( '<img>' ).addClass( 'pdfcreator-header-preview-image' );
	const $tableCnt = $( '<div>' ).addClass( 'pdfcreator-header-preview' );
	const $table = $( '<table>' ).addClass( 'pdfcreator-preview-table-header' );
	const $tableRow = $( '<tr>' );
	this.$leftTd = $( '<td>' ).addClass( 'pdfcreator-preview-table-header-left' );
	this.$rightTd = $( '<td>' ).addClass( 'pdfcreator-preview-table-header-right' );
	$tableRow.append( this.$leftTd ).append( this.$rightTd );
	$table.append( $tableRow );
	$tableCnt.append( $table );
	$previewCnt.append( $tableCnt );
	this.$element.append( $previewCnt );

	this.updatePreview();
};

ext.pdfcreator.ui.booklet.pages.Header.prototype.updatePreview = function () {
	this.preparePreview( this.headerText.getValue() ).done( ( result ) => {
		if ( this.headerLayoutInput.findSelectedItem().data === 'left' ) {
			this.$leftTd.html( this.$headerImage );
			this.$rightTd.html( result );
		} else {
			this.$leftTd.html( result );
			this.$rightTd.html( this.$headerImage );
		}
	} );
};

ext.pdfcreator.ui.booklet.pages.Header.prototype.updateImage = function ( fileName ) {
	const dfd = $.Deferred(),
		title = mw.Title.newFromText( 'File:' + fileName );
	if ( !this.useWikiLogo.isSelected() ) {
		this.$headerImage.attr( 'src', this.data.logoPath );
		this.updatePreview();
		dfd.resolve();
		return dfd.promise();
	}
	if ( fileName === '' ) {
		this.$headerImage.attr( 'src', '' );
		this.updatePreview();
		dfd.resolve();
		return dfd.promise();
	}

	mw.loader.using( [ 'mediawiki.api' ] ).done( () => {
		const mwApi = new mw.Api();
		const params = {
			action: 'query',
			format: 'json',
			prop: 'imageinfo',
			iiprop: 'url',
			titles: title.getPrefixedText() ?? ''
		};

		mwApi.get( params ).done( ( data ) => {
			const pages = data.query.pages;
			let url = '';
			for ( const p in pages ) {
				for ( const v in pages[ p ].imageinfo ) {
					url = pages[ p ].imageinfo[ v ].url;
				}
			}
			this.$headerImage.attr( 'src', url );
			this.updatePreview();
			dfd.resolve( url );
		} ).fail( () => {
			dfd.reject();
		} );

	} );
	return dfd.promise();
};

ext.pdfcreator.ui.booklet.pages.Header.prototype.setUploadedImage = function ( filename ) {
	this.headerImage.setValue( filename );
};

ext.pdfcreator.ui.booklet.pages.Header.prototype.toggleUploadPanel = function ( selected ) {
	this.headerImageFileLayout.toggle( selected );
	this.updateImage( this.headerImage.getValue() );
};
