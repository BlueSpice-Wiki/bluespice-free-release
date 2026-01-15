ext.contentdroplets.ui.ContentDropletPage = function ( name, label, droplets ) {
	ext.contentdroplets.ui.ContentDropletPage.parent.call( this, name, {} );
	this.label = label;
	this.droplets = droplets;
	this.setupWidget();
};

OO.inheritClass( ext.contentdroplets.ui.ContentDropletPage, OO.ui.PageLayout );

ext.contentdroplets.ui.ContentDropletPage.prototype.setupOutlineItem = function () {
	ext.contentdroplets.ui.ContentDropletPage.super.prototype.setupOutlineItem.apply(
		this, arguments
	);

	if ( this.outlineItem ) {
		this.outlineItem.setLabel( this.label );
	}
};

ext.contentdroplets.ui.ContentDropletPage.prototype.setupWidget = function () {
	let key, widget;
	this.widgets = [];
	for ( key in this.droplets ) {
		// eslint-disable-next-line no-prototype-builtins
		if ( !this.droplets.hasOwnProperty( key ) ) {
			continue;
		}
		widget = new ext.contentdroplets.ui.DropletWidget( {
			droplet: this.droplets[ key ],
			padded: true,
			expanded: false
		} );
		widget.connect( this, {
			selected: function ( key, widget ) { // eslint-disable-line no-shadow
				this.emit( 'dropletSelected', key, widget );
			}
		} );
		this.widgets[ key ] = widget;
	}

	this.widgetsLayout = new OO.ui.HorizontalLayout( {
		items: Object.values( this.widgets )
	} );

	this.$element.append( this.widgetsLayout.$element );
	this.$element.attr( 'role', 'listbox' );
	this.$element.attr( 'aria-multiselectable', 'false' );
	this.$element.attr( 'tabindex', 0 );
};
