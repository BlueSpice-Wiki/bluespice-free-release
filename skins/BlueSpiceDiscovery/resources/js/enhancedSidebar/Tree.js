window.bs = window.bs || {};
window.bs.skin = bs.skin || {};
bs.skin.enhancedSidebar = bs.skin.enhancedSidebar || {};
bs.skin.enhancedSidebar.tree = bs.skin.enhancedSidebar.tree || {};

bs.skin.enhancedSidebar.tree.EnhancedSidebarTree = function ( cfg ) {
	bs.skin.enhancedSidebar.tree.EnhancedSidebarTree.parent.call( this, cfg );
};

OO.inheritClass( bs.skin.enhancedSidebar.tree.EnhancedSidebarTree, ext.menueditor.ui.data.tree.Tree );

bs.skin.enhancedSidebar.tree.EnhancedSidebarTree.prototype.getPossibleNodesForLevel = function ( lvl ) {
	if ( lvl === 0 ) {
		return [ 'enhanced-sidebar-panel-heading' ];
	}
	return [ 'enhanced-sidebar-external-link', 'enhanced-sidebar-internal-link', 'enhanced-sidebar-subpage-tree' ];
};

bs.skin.enhancedSidebar.tree.EnhancedSidebarTree.prototype.getMaxLevels = function () {
	// Just a sanity limit - TBD
	return 10;
};
