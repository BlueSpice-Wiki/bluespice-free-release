ext.menueditor.ui.data.node.WikilinkNode = function ( cfg ) {
	ext.menueditor.ui.data.node.WikilinkNode.parent.call( this, cfg );
};

OO.inheritClass( ext.menueditor.ui.data.node.WikilinkNode, ext.menueditor.ui.data.node.TreeNode );

ext.menueditor.ui.data.node.WikilinkNode.static.canHaveChildren = false;

ext.menueditor.ui.data.node.WikilinkNode.prototype.labelFromData = function ( data ) {
	if ( data.label ) {
		return data.label + ' (' + data.target + ')';
	}
	return data.target;
};

// eslint-disable-next-line no-unused-vars
ext.menueditor.ui.data.node.WikilinkNode.prototype.getIcon = function ( data ) {
	return 'wikiText';
};

ext.menueditor.ui.data.node.WikilinkNode.prototype.getFormFields = function ( dialog ) {
	return [
		{
			name: 'target',
			type: 'title',
			// eslint-disable-next-line camelcase
			widget_$overlay: dialog.$overlay,
			required: true,
			label: mw.message( 'menueditor-ui-form-field-target' ).text(),
			help: mw.message( 'menueditor-ui-menu-wiki-link-help' ).text()
		},
		{
			name: 'label',
			type: 'text',
			label: mw.message( 'menueditor-ui-form-field-label' ).text()
		}
	];
};
