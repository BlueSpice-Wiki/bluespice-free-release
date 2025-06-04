flexiskin.ui.Configurator = function ( cfg ) {
	cfg = cfg || {};

	this.skin = cfg.skin;
	this.active = this.skin.active || false;
	this.skin.config = this.skin.config || {};

	flexiskin.ui.Configurator.parent.call( this );

	this.$element.addClass( 'fs-configure' );

	this.$messageCnt = $( '<div>' );
	this.$element.append( this.$messageCnt );

	if ( !this.active && this.skin.id !== null ) {
		this.makeDisabledWarning();
	}
	this.makeToolbar();
	this.initPlugins();
	this.setValue( this.skin.config );
	this.makeForm();

	setTimeout( () => {
		// Run on another event loop go
		this.emit( 'renderComplete', this );
	}, 1 );
};

OO.inheritClass( flexiskin.ui.Configurator, OO.ui.Widget );

flexiskin.ui.Configurator.static.tagName = 'div';
flexiskin.ui.Configurator.static.VISIBILITY_BASIC = 'basic';
flexiskin.ui.Configurator.static.VISIBILITY_ADVANCED = 'advanced';

flexiskin.ui.Configurator.prototype.initPlugins = function () {
	this.plugins = {};
	this.controls = {};
	this.items = {};
	for ( const pluginId in flexiskin.registry.Plugin.registry ) {
		if ( !flexiskin.registry.Plugin.registry.hasOwnProperty( pluginId ) ) {
			continue;
		}

		this.plugins[ pluginId ] = new flexiskin.registry.Plugin.registry[ pluginId ]();
		const flatlist = this.plugins[ pluginId ].getFlatList();
		for ( const id in flatlist ) {
			if ( !flatlist.hasOwnProperty( id ) ) {
				continue;
			}
			const callback = this.getCallbackFor( 'init', flatlist[ id ] );
			if ( callback ) {
				callback.call( flatlist[ id ], this, id );
			}
		}

		this.items = $.extend( true, {}, this.items, flatlist );
		this.controls = $.extend( true, {}, this.controls, this.plugins[ pluginId ].getControls() );
	}
};

flexiskin.ui.Configurator.prototype.makeDisabledWarning = function () {
	this.$element.append( new OO.ui.MessageWidget( {
		type: 'warning',
		label: mw.message( 'flexiskin-ui-configurator-warning-disabled-skin' ).text()
	} ).$element );
};

flexiskin.ui.Configurator.prototype.makeForm = function () {
	const grouped = this.groupItems(),
		items = [];
	this.formElements = {};

	for ( const groupId in grouped ) {
		if ( !grouped.hasOwnProperty( groupId ) ) {
			continue;
		}
		const layouts = this.getGroupLayouts( grouped[ groupId ] );
		this.formElements[ groupId ] = new flexiskin.ui.ConfigGroup( groupId, {
			label: this.getItemGroupLabel( groupId ),
			expanded: this.getItemGroupExpanded( groupId ),
			items: layouts
		} );
		items.push( this.formElements[ groupId ].$element );
	}

	this.emit( 'makeFormComplete', this.formElements );
	this.$element.append( items );
};

flexiskin.ui.Configurator.prototype.getGroupLayouts = function ( items ) {
	const fieldLayouts = {},
		roots = [];

	for ( const id in items ) {
		if ( !items.hasOwnProperty( id ) ) {
			continue;
		}

		const groups = this.getGroupsForItem( id );
		const rootGroup = groups[ 0 ];
		// Remove root group since we already group by it
		if ( groups.length > 1 ) {
			groups.shift();
		}
		let lastGroup;
		for ( let i = 0; i < groups.length; i++ ) {
			if ( i === 0 ) {
				roots.push( groups[ i ] );
			}
			const layout = fieldLayouts[ groups[ i ] ] || new OO.ui.FieldsetLayout( {
				label: rootGroup && rootGroup === groups[ i ] ? '' : this.getItemGroupLabel( groups[ i ] ),
				data: {
					group: groups[ i ]
				}
			} );
			if ( i === ( groups.length - 1 ) ) {
				const data = items[ id ].getData();
				let label = '';
				if ( data && data.hasOwnProperty( 'label' ) ) {
					label = data.label;
				}
				layout.addItems( [ new OO.ui.FieldLayout( items[ id ], {
					label: label,
					align: 'top'
				} ) ] );
			}
			if ( !fieldLayouts.hasOwnProperty( groups[ i ] ) ) {
				fieldLayouts[ groups[ i ] ] = layout;
				if ( lastGroup && fieldLayouts.hasOwnProperty( lastGroup ) ) {
					fieldLayouts[ lastGroup ].addItems( [ layout ] );
				}
			}
			lastGroup = groups[ i ];
		}
	}

	for ( const layoutGroupId in fieldLayouts ) {
		if ( roots.indexOf( layoutGroupId ) === -1 ) {
			delete ( fieldLayouts[ layoutGroupId ] );
		}
	}
	return Object.values( fieldLayouts );
};

flexiskin.ui.Configurator.prototype.groupItems = function () {
	const grouped = {};
	let itemId, groups, root;

	for ( itemId in this.items ) {
		if ( !this.items.hasOwnProperty( itemId ) ) {
			continue;
		}
		groups = this.getGroupsForItem( itemId );
		root = groups.shift();

		if ( !grouped.hasOwnProperty( root ) ) {
			grouped[ root ] = {};
		}
		grouped[ root ][ itemId ] = this.items[ itemId ];
	}

	return grouped;
};

flexiskin.ui.Configurator.prototype.getGroupsForItem = function ( id ) {
	const bits = id.split( '/' );
	bits.pop();
	if ( bits.length === 0 ) {
		return [];
	}

	return bits;
};

flexiskin.ui.Configurator.prototype.getVisibilityForItem = function ( id ) {
	const widget = this.items[ id ];
	const data = widget.getData();
	if ( data && data.hasOwnProperty( 'visibility' ) ) {
		return data.visibility;
	}
	return flexiskin.ui.Configurator.static.VISIBILITY_ADVANCED;
};

flexiskin.ui.Configurator.prototype.getItems = function ( group ) {
	if ( group ) {
		const items = {};
		for ( const id in this.items ) {
			if ( !this.items.hasOwnProperty( id ) ) {
				continue;
			}
			if ( !id.startsWith( group + '/' ) ) {
				continue;
			}
			items[ id ] = this.items[ id ];
		}

		return items;
	}

	return this.items;
};

flexiskin.ui.Configurator.prototype.getItemGroupLabel = function ( group ) {

	const item = this.findGroupByKey( this.controls, group );

	if ( item && item.hasOwnProperty( 'label' ) ) {
		return item.label;
	}

	return '';
};

flexiskin.ui.Configurator.prototype.getItemGroupExpanded = function ( group ) {
	const item = this.findGroupByKey( this.controls, group );

	if ( item && item.hasOwnProperty( 'expanded' ) ) {
		return !!item.expanded;
	}

	return false;
};

flexiskin.ui.Configurator.prototype.findGroupByKey = function ( obj, key ) {
	const keys = Object.keys( obj );
	let value;

	for ( let i = 0; i < keys.length; i++ ) {
		if ( keys[ i ] === key ) {
			return obj[ key ];
		} else if ( !( obj[ keys[ i ] ] instanceof OO.ui.Widget ) && $.type( obj[ keys[ i ] ] ) === 'object' ) { // eslint-disable-line no-jquery/no-type
			value = this.findGroupByKey( obj[ keys[ i ] ], key );
			if ( value ) {
				return value;
			}
		}
	}

	return value;
};

flexiskin.ui.Configurator.prototype.getValue = function ( forAction ) {
	const value = {},
		dfd = $.Deferred(),
		itemsToResolve = Object.assign( {}, this.items );

	this.getValueForItems( dfd, value, itemsToResolve, forAction );

	return dfd.promise();
};

flexiskin.ui.Configurator.prototype.getValueForItems = function ( dfd, value, items, forAction ) {
	if ( $.isEmptyObject( items ) ) {
		return dfd.resolve( value );
	}
	const itemId = Object.keys( items ).shift(),
		item = items[ itemId ];
	delete ( items[ itemId ] );
	const callback = this.getCallbackFor( forAction, item );
	if ( callback ) {
		callback.call( item, {}, itemId )
			.done( ( response ) => {
				this.setToPath( value, itemId.split( '/' ), response );
				return this.getValueForItems( dfd, value, items, forAction );
			} )
			.fail( () => this.getValueForItems( dfd, value, items, forAction ) );
	} else {
		this.setToPath( value, itemId.split( '/' ), item.getValue() );
		return this.getValueForItems( dfd, value, items, forAction );
	}
};

flexiskin.ui.Configurator.prototype.setValue = function ( value ) {
	if ( !value ) {
		return;
	}

	this.value = value;

	for ( const itemId in this.items ) {
		const itemValue = this.getByPath( $.extend( true, {}, value ), itemId.split( '/' ) );
		if ( itemValue ) {
			const callback = this.getCallbackFor( 'setValue', this.items[ itemId ] );
			if ( callback ) {
				callback.call( this.items[ itemId ], itemValue );
			} else {
				this.items[ itemId ].setValue( itemValue );
			}
		}
	}
};

flexiskin.ui.Configurator.prototype.getByPath = function ( obj, path ) {
	const bit = path.shift();
	if ( $.type( obj ) !== 'object' || !obj.hasOwnProperty( bit ) ) { // eslint-disable-line no-jquery/no-type
		return null;
	}
	if ( path.length === 0 ) {
		return obj[ bit ];
	}
	obj = obj[ bit ];
	return this.getByPath( obj, path );
};

flexiskin.ui.Configurator.prototype.getCallbackFor = function ( action, item ) {
	const data = item.getData();
	if ( $.type( data ) !== 'object' ) { // eslint-disable-line no-jquery/no-type
		return null;
	}

	if ( !data.hasOwnProperty( 'actionCallback' ) ) {
		return null;
	}
	if ( !data.actionCallback.hasOwnProperty( action ) ) {
		return null;
	}

	return data.actionCallback[ action ];
};

flexiskin.ui.Configurator.prototype.setToPath = function ( obj, path, value, append ) {
	const bit = path.shift();
	if ( path.length === 0 ) {
		if ( append ) {
			obj[ bit ] = obj[ bit ] || [];
			obj[ bit ].push( value );
		} else {
			obj[ bit ] = value;
		}
		return obj;
	}
	obj[ bit ] = obj[ bit ] || {};
	this.setToPath( obj[ bit ], path, value, append );
};

flexiskin.ui.Configurator.prototype.makeToolbar = function () {
	this.toolbar = new OOJSPlus.ui.toolbar.ManagerToolbar( {
		actions: [
			new OOJSPlus.ui.toolbar.tool.ToolbarTool( {
				name: 'preview',
				title: mw.msg( 'flexiskin-ui-configurator-button-preview-label' )
			} ),
			new OOJSPlus.ui.toolbar.tool.ToolbarTool( {
				name: 'delete',
				flags: [ 'destructive', 'primary' ],
				title: mw.msg( 'flexiskin-ui-configurator-button-disable-label' )
			} ),
			new OOJSPlus.ui.toolbar.tool.ToolbarTool( {
				name: 'save',
				flags: [ 'primary', 'progressive' ],
				title: mw.msg( 'flexiskin-ui-configurator-button-save-label' )
			} )
		]
	} );
	this.$element.append( this.toolbar.$element );
	this.toolbar.connect( this, {
		action: 'onAction'
	} );
	this.toolbar.setup();
	this.toolbar.initialize();
};

flexiskin.ui.Configurator.prototype.onAction = function ( action ) {
	if ( action === 'preview' ) {
		this.doPreview();
	}
	if ( action === 'save' ) {
		this.doSave();
	}
	if ( action === 'delete' ) {
		this.doDisable();
	}
};

flexiskin.ui.Configurator.prototype.doPreview = function () {
	this.clearPreview();

	const loadingDialog = new flexiskin.ui.dialog.PreviewLoading( { size: 'small' } ),
		windowManager = OO.ui.getWindowManager();
	windowManager.addWindows( [ loadingDialog ] );
	windowManager.openWindow( loadingDialog );
	this.getValue( 'preview' ).done( ( value ) => {
		new mw.Api().get( {
			action: 'flexiskin-preview',
			config: JSON.stringify( value ),
			_dc: Date.now()
		} ).done( ( response ) => {
			if ( response.hasOwnProperty( 'loadData' ) ) {
				$.get( mw.util.wikiScript( 'load' ), {
					modules: response.loadData.modules.join( '|' ),
					vars: JSON.stringify( response.loadData.vars ),
					only: 'styles'
				} ).done( ( data ) => {
					// TODO: This will keep on adding new versions - not a big deal, but not nice
					$( 'style' ).append( data );
					windowManager.closeWindow( loadingDialog );
				} ).fail( function () {
					windowManager.closeWindow( loadingDialog );
					this.previewError();
				} );
			}
			windowManager.closeWindow( loadingDialog );
		} ).fail( function () {
			windowManager.closeWindow( loadingDialog );
			this.previewError();
		} );
	} );

};

flexiskin.ui.Configurator.prototype.doSave = function () {
	this.clearPreview();

	const confirmationMessage = mw.message( 'flexiskin-configurator-prompt-on-save' ).text();
	OO.ui.confirm( confirmationMessage, { size: 'large' } ).done( ( confirmed ) => {
		if ( confirmed ) {
			this.getValue( 'save' ).done( ( value ) => {
				new mw.Api().get( {
					action: 'flexiskin-save',
					skinname: this.skin.name,
					config: JSON.stringify( value )
				} ).done( ( response ) => {
					if ( !response.hasOwnProperty( 'success' ) || !response.success ) {
						this.saveError();
						return;
					}
					this.debugReload();
				} ).fail( () => {
					this.saveError();
				} );
			} );
		}
	} );

};

flexiskin.ui.Configurator.prototype.doDisable = function () {
	const confirmationMessage = mw.message( 'flexiskin-configurator-prompt-on-disable' ).text();
	OO.ui.confirm( confirmationMessage, { size: 'large' } ).done( ( confirmed ) => {
		if ( confirmed ) {
			new mw.Api().get( {
				action: 'flexiskin-activation',
				skinname: this.skin.name,
				active: 0
			} ).done( ( response ) => {
				if ( !response.hasOwnProperty( 'success' ) || !response.success ) {
					this.resetError();
					return;
				}
				this.debugReload();
			} ).fail( () => {
				this.disableError();
			} );
		}

	} );
};

flexiskin.ui.Configurator.prototype.debugReload = function () {
	// Refresh to apply changes
	// Add debug=true param - works for cache, but breaks other things
	const url = new URL( window.location.href );
	url.searchParams.set( 'debug', 'true' );
	window.location.href = url.href;
};

flexiskin.ui.Configurator.prototype.clearPreview = function () {
	// TODO: Clear data from previous preview
};

flexiskin.ui.Configurator.prototype.previewError = function () {
	OO.ui.alert( mw.message( 'flexiskin-ui-error-preview-fail' ).text() );
};

flexiskin.ui.Configurator.prototype.saveError = function () {
	OO.ui.alert( mw.message( 'flexiskin-ui-error-save-fail' ).text() );
};

flexiskin.ui.Configurator.prototype.disableError = function () {
	OO.ui.alert( mw.message( 'flexiskin-ui-error-disable-fail' ).text() );
};
