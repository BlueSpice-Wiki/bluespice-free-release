window.bs = window.bs || {};
window.bs.skin = bs.skin || {};
bs.skin.enhancedSidebar = bs.skin.enhancedSidebar || {};
bs.skin.enhancedSidebar.tree = bs.skin.enhancedSidebar.tree || {};
bs.skin.enhancedSidebar.tree.node = bs.skin.enhancedSidebar.tree.node || {};

bs.skin.enhancedSidebar.tree.node.PanelHeading = function ( cfg ) {
	bs.skin.enhancedSidebar.tree.node.PanelHeading.parent.call( this, cfg );
};

OO.inheritClass( bs.skin.enhancedSidebar.tree.node.PanelHeading, bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode );

bs.skin.enhancedSidebar.tree.node.PanelHeading.static.canHaveChildren = true;

bs.skin.enhancedSidebar.tree.node.PanelHeading.prototype.labelFromData = function ( data ) {
	return data.text;
};

bs.skin.enhancedSidebar.tree.node.PanelHeading.prototype.getIcon = function () {
	return 'largerText';
};

bs.skin.enhancedSidebar.tree.node.PanelHeading.prototype.getFormFields = function ( dialog ) {
	const fields = bs.skin.enhancedSidebar.tree.node.PanelHeading.parent.prototype.getFormFields.call( this, dialog );

	// Find field with name `text` and modify it
	for ( let i = 0; i < fields.length; i++ ) {
		if ( fields[ i ].name === 'text' ) {
			fields[ i ].label = mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-heading' ).text();
			fields[ i ].help = mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-heading-help' ).text();
			break;
		}
	}

	return fields;
};
