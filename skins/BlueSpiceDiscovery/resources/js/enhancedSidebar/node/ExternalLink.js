window.bs = window.bs || {};
window.bs.skin = bs.skin || {};
bs.skin.enhancedSidebar = bs.skin.enhancedSidebar || {};
bs.skin.enhancedSidebar.tree = bs.skin.enhancedSidebar.tree || {};
bs.skin.enhancedSidebar.tree.node = bs.skin.enhancedSidebar.tree.node || {};

bs.skin.enhancedSidebar.tree.node.ExternalLink = function ( cfg ) {
	bs.skin.enhancedSidebar.tree.node.ExternalLink.parent.call( this, cfg );
};

OO.inheritClass( bs.skin.enhancedSidebar.tree.node.ExternalLink, bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode );

bs.skin.enhancedSidebar.tree.node.ExternalLink.static.canHaveChildren = true;

bs.skin.enhancedSidebar.tree.node.ExternalLink.prototype.labelFromData = function ( data ) {
	return data.text;
};

bs.skin.enhancedSidebar.tree.node.ExternalLink.prototype.getIcon = function () {
	return 'linkExternal';
};

bs.skin.enhancedSidebar.tree.node.ExternalLink.prototype.getCustomFormFields = function ( dialog ) { // eslint-disable-line no-unused-vars
	return [
		{
			name: 'href',
			type: 'text',
			required: true,
			widget_icon: 'linkExternal',
			label: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-href' ).text(),
			help: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-href-help' ).text()
		}
	];
};
