( function ( mw ) {
	mw.ext.forms.mixin.IndexLayoutParser = function () {};

	OO.initClass( mw.ext.forms.mixin.IndexLayoutParser );

	mw.ext.forms.mixin.IndexLayoutParser.prototype.parseIndexLayout = function ( tabs, index ) {
		for ( let i = 0; i < tabs.length; i++ ) {
			const tab = tabs[ i ];
			if ( this.shouldShow( tab.showOn ) === false ) {
				continue;
			}

			const name = tab.name;
			const contLayout = new OO.ui.FieldsetLayout();
			const tabPanel = new OO.ui.TabPanelLayout( name, {
				expanded: false,
				framed: false,
				padded: true,
				label: tab.label,
				content: [ contLayout ]
			} );
			const items = tab.items || tab.widget_items;

			this.parseItems( items || [], contLayout );

			index.addTabPanels( [ tabPanel ] );
		}
	};

}( mediaWiki ) );
