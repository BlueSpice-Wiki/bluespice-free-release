window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui = window.enhancedUpload.ui || {};
window.enhancedUpload.ui.widget = window.enhancedUpload.ui.widget || {};

enhancedUpload.ui.widget.DetailsUploadWidget = function ( cfg ) {
	cfg = cfg || {};
	enhancedUpload.ui.widget.DetailsUploadWidget.parent.call( this, cfg );

	this.categoryInput = new OOJSPlus.ui.widget.CategoryMultiSelectWidget( {
		allowArbitrary: true,
		$overlay: this.$overlay
	} );
	if ( cfg.categories ) {
		this.setDefaultCategories( cfg.categories );
	}

	const categoryInputField = new OO.ui.FieldLayout( this.categoryInput, {
		label: mw.message( 'enhancedupload-details-categories-label' ).plain(),
		align: 'left'
	} );

	this.descriptionInput = new OO.ui.MultilineTextInputWidget( {
		rows: 5
	} );
	if ( cfg.description ) {
		this.setDescription( cfg.description );
	}
	const descriptionInputField = new OO.ui.FieldLayout( this.descriptionInput, {
		label: mw.message( 'enhancedupload-details-description-label' ).plain(),
		align: 'left'
	} );

	this.detailsWidget = new OO.ui.Widget( {
		classes: [ 'details-widget' ]
	} );

	this.$element.append( categoryInputField.$element );
	this.$element.append( descriptionInputField.$element );
};

OO.inheritClass( enhancedUpload.ui.widget.DetailsUploadWidget, OO.ui.Widget );

enhancedUpload.ui.widget.DetailsUploadWidget.prototype.setToDefault = function () {
	this.descriptionInput.setValue( '' );
	this.categoryInput.clearItems();
};

enhancedUpload.ui.widget.DetailsUploadWidget.prototype.getDescription = function () {
	return this.descriptionInput.getValue();
};

enhancedUpload.ui.widget.DetailsUploadWidget.prototype.setDescription = function ( description ) {
	this.descriptionInput.setValue( description );
};

enhancedUpload.ui.widget.DetailsUploadWidget.prototype.getCategories = function () {
	let categories = '';
	for ( let i = 0; i < this.categoryInput.getSelectedCategories().length; i++ ) {
		const catText = this.categoryInput.getSelectedCategories()[ i ];
		const catTitle = mw.Title.newFromText( catText, 14 );
		const cat = '[[' + catTitle.getPrefixedText() + ']] ';
		categories += cat;
	}
	return categories;
};

enhancedUpload.ui.widget.DetailsUploadWidget.prototype.setDefaultCategories =
	function ( category ) {
		let categories = [];
		categories = category.split( '|' );
		this.categoryInput.setValue( categories );
	};
