( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.Multiplier = function( cfg ) {
		mw.ext.forms.widget.view.Multiplier.parent.call( this, cfg );

		this.base = cfg.base;
		this.form = cfg.form;

		this.itemsLayout = new OO.ui.FieldsetLayout( {
			label: this.label,
			classes: [ 'multiplier-items-layout' ]
		} );


		this.$element.append( this.itemsLayout.$element );
		this.$element.addClass( 'ext-forms-widget-multiplier' );
	};

	OO.inheritClass( mw.ext.forms.widget.view.Multiplier, OO.ui.Widget );

	mw.ext.forms.widget.view.Multiplier.prototype.setValue = function( value ) {
		if ( !$.isArray( value ) ) {
			return;
		}
		for ( var i = 0; i < value.length; i++ ) {
			var item = value[i];
			var itemLayout = new OO.ui.FieldsetLayout( {
				data: {},
				classes: [ 'multiplier-item' ]
			} );
			var parsedItems = this.form.parseItems( this.base, itemLayout, true );
			for ( var key in parsedItems ) {
				if ( !parsedItems.hasOwnProperty( key ) ) {
					continue;
				}
				if ( item.hasOwnProperty( key ) )  {
					parsedItems[key].setValue( item[key] );
				}
			}
			this.itemsLayout.addItems( [ itemLayout ] );
		}
	};
} )( mediaWiki, jQuery );
