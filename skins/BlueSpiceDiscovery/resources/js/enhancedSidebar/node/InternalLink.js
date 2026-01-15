window.bs = window.bs || {};
window.bs.skin = bs.skin || {};
bs.skin.enhancedSidebar = bs.skin.enhancedSidebar || {};
bs.skin.enhancedSidebar.tree = bs.skin.enhancedSidebar.tree || {};
bs.skin.enhancedSidebar.tree.node = bs.skin.enhancedSidebar.tree.node || {};

bs.skin.enhancedSidebar.tree.node.InternalLink = function ( cfg ) {
	bs.skin.enhancedSidebar.tree.node.InternalLink.parent.call( this, cfg );
};

OO.inheritClass( bs.skin.enhancedSidebar.tree.node.InternalLink, bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode );

bs.skin.enhancedSidebar.tree.node.InternalLink.static.canHaveChildren = true;

bs.skin.enhancedSidebar.tree.node.InternalLink.prototype.labelFromData = function ( data ) {
	return data.text;
};

bs.skin.enhancedSidebar.tree.node.InternalLink.prototype.getIcon = function () {
	return 'link';
};

bs.skin.enhancedSidebar.tree.node.InternalLink.prototype.getCustomFormFields = function ( dialog ) { // eslint-disable-line no-unused-vars
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
		}
	];
};
