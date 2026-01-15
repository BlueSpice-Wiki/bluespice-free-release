ext.menueditor.ui.data.tree.FooterLinksTree = function ( cfg ) {
	ext.menueditor.ui.data.tree.FooterLinksTree.parent.call( this, cfg );
};

OO.inheritClass( ext.menueditor.ui.data.tree.FooterLinksTree, ext.menueditor.ui.data.tree.Tree );

ext.menueditor.ui.data.tree.FooterLinksTree.prototype.getPossibleNodesForLevel = function ( lvl ) {
	switch ( lvl ) {
		case 0:
			return [ 'menu-two-fold-link-spec' ];
		default:
			return [];
	}
};

ext.menueditor.ui.data.tree.FooterLinksTree.prototype.getMaxLevels = function () {
	return 1;
};
