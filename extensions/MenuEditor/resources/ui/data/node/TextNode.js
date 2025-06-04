ext.menueditor.ui.data.node.TextNode = function ( cfg ) {
	ext.menueditor.ui.data.node.TextNode.parent.call( this, cfg );
};

OO.inheritClass( ext.menueditor.ui.data.node.TextNode, ext.menueditor.ui.data.node.TreeNode );

ext.menueditor.ui.data.node.TextNode.static.canHaveChildren = true;

ext.menueditor.ui.data.node.TextNode.prototype.labelFromData = function ( data ) {
	return data.text;
};

ext.menueditor.ui.data.node.TextNode.prototype.getIcon = function () {
	return 'textLanguage';
};

// eslint-disable-next-line no-unused-vars
ext.menueditor.ui.data.node.TextNode.prototype.getFormFields = function ( dialog ) {
	return [
		{
			name: 'text',
			type: 'text',
			required: true,
			label: mw.message( 'menueditor-ui-form-field-text' ).text(),
			help: mw.message( 'menueditor-ui-menu-raw-text-help' ).text()
		}
	];
};
