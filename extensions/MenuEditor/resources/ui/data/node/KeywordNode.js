ext.menueditor.ui.data.node.KeywordNode = function ( cfg ) {
	ext.menueditor.ui.data.node.KeywordNode.parent.call( this, cfg );
};

OO.inheritClass( ext.menueditor.ui.data.node.KeywordNode, ext.menueditor.ui.data.node.TextNode );

ext.menueditor.ui.data.node.KeywordNode.static.canHaveChildren = false;

ext.menueditor.ui.data.node.KeywordNode.prototype.labelFromData = function ( data ) {
	return data.keyword;
};

// eslint-disable-next-line no-unused-vars
ext.menueditor.ui.data.node.KeywordNode.prototype.getIcon = function ( data ) {
	return 'markup';
};

ext.menueditor.ui.data.node.KeywordNode.prototype.getFormFields = function ( dialog ) {
	return [
		{
			name: 'keyword',
			type: 'dropdown',
			options: [
				{ data: 'SEARCH' },
				{ data: 'TOOLBOX' },
				{ data: 'LANGUAGES' },
				{ data: 'PAGESVISITED' },
				{ data: 'YOUREDITS' }
			],
			// eslint-disable-next-line camelcase
			widget_$overlay: dialog.$overlay,
			required: true,
			label: mw.message( 'menueditor-ui-form-field-keyword' ).text(),
			help: mw.message( 'menueditor-ui-menu-keyword-help' ).text()
		}
	];
};

ext.menueditor.ui.data.node.KeywordNode.prototype.shouldRender = function () {
	return true;
};
