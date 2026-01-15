window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui = window.enhancedUpload.ui || {};
window.enhancedUpload.ui.panel = window.enhancedUpload.ui.panel || {};

enhancedUpload.ui.panel.Preview = function enhancedUploadUiPanelPreview() {
	const cfg = {};
	cfg.orientation = 'horizontal';
	cfg.draggable = true;
	cfg.classes = [ 'file-preview', 'no-files' ];

	enhancedUpload.ui.panel.Preview.parent.call( this, cfg );
	OO.ui.mixin.GroupElement.call( this, Object.assign( {}, cfg, { $group: this.$element } ) );

	this.items = [];
	this.itemData = [];
};

OO.inheritClass( enhancedUpload.ui.panel.Preview, OO.ui.Widget );
OO.mixinClass( enhancedUpload.ui.panel.Preview, OO.ui.mixin.GroupElement );

enhancedUpload.ui.panel.Preview.static.label = '';

enhancedUpload.ui.panel.Preview.static.tagName = 'div';

enhancedUpload.ui.panel.Preview.prototype.addToPreview = function ( value, url ) {
	let itemWidget;
	this.itemData.push( {
		data: value,
		url: url
	} );
	if ( url ) {
		itemWidget = new enhancedUpload.ui.panel.PreviewWidget( {
			name: value.name,
			classes: [ 'thumbnail-items', 'thumbnail-preview' ],
			url: url
		} );
	} else {
		itemWidget = new enhancedUpload.ui.panel.PreviewWidget( {
			name: value.name,
			icon: 'attachment',
			classes: [ 'thumbnail-items', 'thumbnail-icon' ]
		} );
	}

	itemWidget.connect( this, {
		remove: 'removeItemFromPreview'
	} );

	this.addItems( [ itemWidget ] );
};

enhancedUpload.ui.panel.Preview.prototype.showPreview = function () {
	$( this.$element ).removeClass( 'no-files' );
};

enhancedUpload.ui.panel.Preview.prototype.clearPreview = function () {
	$( this.$element ).addClass( 'no-files' );
	this.removeItems( this.items );
	this.clearItems();
	this.items = [];
	this.itemData = [];
};

enhancedUpload.ui.panel.Preview.prototype.removeItemFromPreview = function ( widget ) {
	if ( !this.items.length ) {
		return;
	}
	if ( this.items.length === 1 ) {
		this.clearPreview();
		this.emit( 'updateElements', 0 );
		return;
	}

	const index = this.items.indexOf( widget );

	this.removeItems( widget );
	widget.$element.hide();

	this.itemData.splice( index, 1 );
};

enhancedUpload.ui.panel.Preview.prototype.getFiles = function () {
	return this.itemData;
};
