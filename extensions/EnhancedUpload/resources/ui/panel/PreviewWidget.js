window.enhancedUpload = window.enhancedUpload || {};
window.enhancedUpload.ui.panel = window.enhancedUpload.ui.panel || {};

enhancedUpload.ui.panel.PreviewWidget = function ( cfg ) {
	enhancedUpload.ui.panel.PreviewWidget.parent.call( this, cfg );
	cfg.indicator = 'clear';
	this.name = cfg.name;

	cfg.popup = {
		$content: this.getPopupContent(),
		$overlay: true
	};

	OO.ui.mixin.IndicatorElement.call( this, cfg );
	OO.ui.mixin.ButtonElement.call( this, cfg );
	OO.ui.mixin.PopupElement.call( this, cfg );

	this.$button.append( this.$indicator );
	this.$button.attr( 'tabindex', 0 );
	this.$button.attr( 'aria-label', mw.message( 'enhancedupload-preview-widget-remove-label', this.name ).plain() );

	this.connect( this, {
		click: 'removeItem'
	} );

	if ( cfg.url ) {
		this.$element.css( 'background-image', 'url( ' + cfg.url + ' )' );
	} else {
		OO.ui.mixin.IconElement.call( this, cfg );
		this.$element.append( this.$icon );
	}

	this.popup.$element.addClass( 'oo-ui-popupButtonWidget-popup preview-label' );

	this.$element
		.attr( 'aria-haspopup', 'true' )
		.append( this.$button, this.popup.$element );

};

OO.inheritClass( enhancedUpload.ui.panel.PreviewWidget, OO.ui.Widget );
OO.mixinClass( enhancedUpload.ui.panel.PreviewWidget, OO.ui.mixin.IconElement );
OO.mixinClass( enhancedUpload.ui.panel.PreviewWidget, OO.ui.mixin.ButtonElement );
OO.mixinClass( enhancedUpload.ui.panel.PreviewWidget, OO.ui.mixin.IndicatorElement );
OO.mixinClass( enhancedUpload.ui.panel.PreviewWidget, OO.ui.mixin.PopupElement );

enhancedUpload.ui.panel.PreviewWidget.static.label = '';

enhancedUpload.ui.panel.PreviewWidget.static.tagName = 'div';

enhancedUpload.ui.panel.PreviewWidget.prototype.removeItem = function () {
	this.emit( 'remove', this );
};

enhancedUpload.ui.panel.PreviewWidget.prototype.getPopupContent = function () {
	return new OO.ui.LabelWidget( {
		label: this.name
	} ).$element;
};
