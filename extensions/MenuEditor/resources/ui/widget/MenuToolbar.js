ext.menueditor.ui.widget.MenuToolbar = function ( cfg ) {
	const toolConfig = require( './tools.json' );
	this.toolbarItems = [ 'newItem', 'cancel', 'save' ].concat( cfg.toolbarItems );
	this.tools = this.getTools( toolConfig.tools );
	this.modules = this.getToolsModules( toolConfig.modules );

	this.toolFactory = new OO.ui.ToolFactory();
	this.toolGroupFactory = new OO.ui.ToolGroupFactory();
	this.toolbar = new OO.ui.Toolbar( this.toolFactory, this.toolGroupFactory, {
		classes: [ 'menueditor-toolbar' ]
	} );
	mw.loader.using( this.modules ).done( () => {
		this.addNewItemTool();
		const groups = this.buildGroups();
		this.toolbar.setup( groups );

		this.toolbar.initialize();
		this.toolbar.emit( 'updateState' );
	} );
};

ext.menueditor.ui.widget.MenuToolbar.prototype.addNewItemTool = function () {
	for ( const tool in this.tools ) {
		this.toolFactory.register( this.callbackFromString( this.tools[ tool ].classname ) );
	}
};

ext.menueditor.ui.widget.MenuToolbar.prototype.callbackFromString = function ( callback ) {
	const parts = callback.split( '.' );
	let func = window[ parts[ 0 ] ];
	for ( let i = 1; i < parts.length; i++ ) {
		func = func[ parts[ i ] ];
	}

	return func;
};

ext.menueditor.ui.widget.MenuToolbar.prototype.getTools = function ( allTools ) {
	const tools = [];
	for ( const tool in allTools ) {
		// eslint-disable-next-line es-x/no-array-prototype-includes
		if ( this.toolbarItems.includes( tool ) ) {
			tools[ tool ] = allTools[ tool ];
		}
	}
	return tools;
};

ext.menueditor.ui.widget.MenuToolbar.prototype.getToolsModules = function ( allModules ) {
	const modules = [];
	for ( const module in allModules ) {
		// eslint-disable-next-line es-x/no-array-prototype-includes
		if ( this.toolbarItems.includes( module ) && !modules.includes( allModules[ module ] ) ) {
			modules.push( allModules[ module ] );
		}
	}
	return modules;
};

ext.menueditor.ui.widget.MenuToolbar.prototype.buildGroups = function () {
	const groups = [];
	const otherTools = [];
	for ( const tool in this.tools ) {
		// eslint-disable-next-line no-prototype-builtins
		if ( !this.tools[ tool ].hasOwnProperty( 'group' ) ||
			!this.tools[ tool ].group.name.length > 0 ) {
			otherTools.push( tool );
			continue;
		}
		const groupName = this.tools[ tool ].group.name;

		const element = groups.find( ( e ) => e.name === groupName );
		if ( element ) {
			element.include.push( tool );
			if ( this.tools[ tool ].group.classes.length > 0 ) {
				element.classes = element.classes.concat( this.tools[ tool ].group.classes );
			}
			continue;
		}
		const index = this.tools[ tool ].group.priority;
		// eslint-disable-next-line mediawiki/class-doc
		groups.splice( index, 0, {
			name: groupName,
			type: 'bar',
			include: [ tool ],
			classes: this.tools[ tool ].group.classes
		} );
	}
	if ( otherTools.length > 0 ) {
		groups.splice( 5, 0, {
			name: 'other',
			type: 'list',
			include: otherTools
		} );
	}

	return groups;
};
