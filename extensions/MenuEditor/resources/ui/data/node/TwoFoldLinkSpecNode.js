ext.menueditor.ui.data.node.TwoFoldLinkSpecNode = function ( cfg ) {
	ext.menueditor.ui.data.node.TwoFoldLinkSpecNode.parent.call( this, cfg );
};

OO.inheritClass( ext.menueditor.ui.data.node.TwoFoldLinkSpecNode, ext.menueditor.ui.data.node.TreeNode );

ext.menueditor.ui.data.node.TwoFoldLinkSpecNode.static.canHaveChildren = false;

ext.menueditor.ui.data.node.TwoFoldLinkSpecNode.prototype.labelFromData = function ( data ) {
	return data.label + ' (' + data.target + ')';
};

// eslint-disable-next-line no-unused-vars
ext.menueditor.ui.data.node.TwoFoldLinkSpecNode.prototype.getIcon = function ( data ) {
	return 'link';
};

ext.menueditor.ui.data.node.TwoFoldLinkSpecNode.prototype.getFormFields = function ( dialog ) {
	return [
		{
			name: 'target',
			type: 'title',
			required: true,
			// eslint-disable-next-line camelcase
			widget_$overlay: dialog.$overlay,
			label: mw.message( 'menueditor-ui-form-field-target' ).text(),
			help: new OO.ui.HtmlSnippet( mw.message( 'menueditor-ui-menu-two-fold-link-spec-help' ).text() )
		},
		{
			name: 'label',
			type: 'text',
			required: true,
			label: mw.message( 'menueditor-ui-form-field-label' ).text()
		}
	];
};
