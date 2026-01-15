bs.util.registerNamespace( 'ext.usersidebar.data' );

ext.usersidebar.data.UsersidebarKeywordNode = function ( cfg ) {
	ext.usersidebar.data.UsersidebarKeywordNode.parent.call( this, cfg );

	const config = require( './config.json' );
	const keywords = config.allowedUserSidebarKeywords;

	this.options = [];
	for ( let i = 0; i < keywords.length; i++ ) {
		const object = {
			data: keywords[ i ]
		};
		this.options.push( object );
	}
};

OO.inheritClass( ext.usersidebar.data.UsersidebarKeywordNode,
	ext.menueditor.ui.data.node.KeywordNode );

ext.usersidebar.data.UsersidebarKeywordNode.prototype.getFormFields = function ( dialog ) {
	return [
		{
			name: 'keyword',
			type: 'dropdown',
			options: this.options,
			// eslint-disable-next-line camelcase
			widget_$overlay: dialog.$overlay,
			required: true,
			label: mw.message( 'menueditor-ui-form-field-keyword' ).text(),
			help: mw.message( 'menueditor-ui-menu-keyword-help' ).text()
		}
	];
};
