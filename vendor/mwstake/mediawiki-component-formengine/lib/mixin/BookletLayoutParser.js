( function ( mw ) {
	mw.ext.forms.mixin.BookletLayoutParser = function () {};

	OO.initClass( mw.ext.forms.mixin.BookletLayoutParser );

	mw.ext.forms.mixin.BookletLayoutParser.prototype.parseBooklet = function ( pages, booklet ) {
		for ( let i = 0; i < pages.length; i++ ) {
			const page = pages[ i ];
			if ( this.shouldShow( page.showOn ) === false ) {
				continue;
			}

			const pageLayout = new mw.ext.forms.widget.BookletPageLayout( page, this );

			booklet.addPages( [ pageLayout ] );
		}
	};

	mw.ext.forms.widget.BookletPageLayout = function ( page, parser ) {
		const contLayout = new OO.ui.FieldsetLayout();
		const items = page.items || page.widget_items;
		this.page = page;

		mw.ext.forms.widget.BookletPageLayout.parent.call( this, page.name, {
			expanded: false,
			padded: true
		} );

		this.$element.append( contLayout.$element );
		parser.parseItems( items, contLayout );
	};

	OO.inheritClass( mw.ext.forms.widget.BookletPageLayout, OO.ui.PageLayout );

	mw.ext.forms.widget.BookletPageLayout.prototype.setupOutlineItem = function () {
		if ( this.page.hasOwnProperty( 'label' ) ) {
			this.outlineItem.setLabel( this.page.label );
		}
		if ( this.page.hasOwnProperty( 'icon' ) ) {
			this.outlineItem.setIcon( this.page.icon );
		}
	};

}( mediaWiki ) );
