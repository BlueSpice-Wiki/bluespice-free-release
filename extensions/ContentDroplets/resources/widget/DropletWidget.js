ext.contentdroplets.ui.DropletWidget = function ( cfg ) {
	ext.contentdroplets.ui.DropletWidget.parent.call( this, cfg );

	this.droplet = cfg.droplet;
	this.pageId = cfg.id || '$';
	this.module = cfg.module || '';

	OO.EventEmitter.call( this );
	OO.ui.mixin.TabIndexedElement.call( this, cfg );

	this.iconElement = new OO.ui.Widget( {
		// eslint-disable-next-line no-jquery/no-parse-html-literal
		content: $( '<div></div>' ),
		classes: [ 'icon-droplet', this.droplet.getIcon() ]
	} );

	this.content = new OO.ui.Widget( {
		padded: true,
		expanded: false,
		classes: [ 'content-droplet' ]
	} );
	this.setupContent();

	this.container = new OO.ui.HorizontalLayout( {
		padded: true,
		items: [
			this.iconElement,
			this.content
		]
	} );
	this.$element.addClass( 'droplet' );
	this.$element.append( this.container.$element );

	this.$element.on( {
		click: this.select.bind( this ),
		keypress: this.onItemKeyPress.bind( this )
	} );
};

OO.inheritClass( ext.contentdroplets.ui.DropletWidget, OO.ui.Widget );
OO.mixinClass( ext.contentdroplets.ui.DropletWidget, OO.EventEmitter );
OO.mixinClass( ext.contentdroplets.ui.DropletWidget, OO.ui.mixin.TabIndexedElement );

ext.contentdroplets.ui.DropletWidget.prototype.onItemKeyPress = function ( e ) {
	if ( e.which === OO.ui.Keys.ENTER ) {
		this.$element.addClass( 'droplet-selected' );
		this.$element.attr( 'aria-selected', true );
		this.emit( 'selected', this.droplet.getKey(), this );
	}
};

ext.contentdroplets.ui.DropletWidget.prototype.select = function () {
	this.$element.addClass( 'droplet-selected' );
	this.emit( 'selected', this.droplet.getKey(), this );
};

ext.contentdroplets.ui.DropletWidget.prototype.deselect = function () {
	this.$element.removeClass( 'droplet-selected' );
	this.$element.attr( 'aria-selected', false );
	this.emit( 'deselected', this.droplet.getKey() );
};

ext.contentdroplets.ui.DropletWidget.prototype.getDropletObject = function () {
	return this.droplet;
};

ext.contentdroplets.ui.DropletWidget.prototype.setupContent = function () {
	this.titleWidget = new OO.ui.LabelWidget( {
		label: this.droplet.getName(),
		classes: [ 'droplet-title' ]
	} );

	this.descWidget = new OO.ui.LabelWidget( {
		label: this.droplet.getDescription(),
		classes: [ 'droplet-text' ]
	} );

	this.fieldsetLayout = new OO.ui.FieldsetLayout( {
		items: [
			this.titleWidget,
			this.descWidget
		]
	} );

	this.content.$element.append( this.fieldsetLayout.$element );
};
