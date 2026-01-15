bs.util.registerNamespace( 'ext.usersidebar.menu' );

ext.usersidebar.menu.UserSidebarTree = function ( cfg ) {
	ext.usersidebar.menu.UserSidebarTree.parent.call( this, cfg );
};

OO.inheritClass( ext.usersidebar.menu.UserSidebarTree,
	ext.menueditor.ui.data.tree.MediawikiSidebarTree );

ext.usersidebar.menu.UserSidebarTree.prototype.getPossibleNodesForLevel = function ( lvl ) {
	switch ( lvl ) {
		case 0:
			return [ 'menu-raw-text', 'menu-user-sidebar-keyword' ];
		case 1:
			return [ 'menu-two-fold-link-spec' ];
		default:
			return [];
	}
};
