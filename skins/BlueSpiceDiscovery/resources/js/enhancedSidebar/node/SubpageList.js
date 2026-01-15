window.bs = window.bs || {};
window.bs.skin = bs.skin || {};
bs.skin.enhancedSidebar = bs.skin.enhancedSidebar || {};
bs.skin.enhancedSidebar.tree = bs.skin.enhancedSidebar.tree || {};
bs.skin.enhancedSidebar.tree.node = bs.skin.enhancedSidebar.tree.node || {};

bs.skin.enhancedSidebar.tree.node.SubpageList = function ( cfg ) {
	bs.skin.enhancedSidebar.tree.node.SubpageList.parent.call( this, cfg );
};

OO.inheritClass( bs.skin.enhancedSidebar.tree.node.SubpageList, bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode );

bs.skin.enhancedSidebar.tree.node.SubpageList.static.canHaveChildren = false;

bs.skin.enhancedSidebar.tree.node.SubpageList.prototype.labelFromData = function ( data ) {
	return data.text;
};

bs.skin.enhancedSidebar.tree.node.SubpageList.prototype.getIcon = function () {
	return 'listBullet';
};

bs.skin.enhancedSidebar.tree.node.SubpageList.prototype.getCustomFormFields = function ( dialog ) { // eslint-disable-line no-unused-vars
	return [
		{
			name: 'page',
			type: 'title',
			widget_validate: function ( value ) {
				return value !== '';
			},
			required: true,
			label: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-page' ).text(),
			help: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-page-help' ).text()
		},
		{
			name: 'depth',
			type: 'number',
			label: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-depth' ).text(),
			help: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-depth-help' ).text(),
			widget_min: 1,
			widget_max: 6
		}
	];
};
