ext.menueditor.ui.panel.MenuPanel = function ( cfg, treeData, menuType ) {
	ext.menueditor.ui.panel.MenuPanel.parent.call( this, cfg );

	this.pagename = cfg.pagename;
	this.toolbarItems = cfg.toolbar;

	this.allowEdit = cfg.mode && cfg.mode === 'edit';
	const classname = ext.menueditor.util.callbackFromString(
		ext.menueditor.registry.menu.registry[ menuType ]
	);

	if ( typeof treeData === 'undefined' && !this.allowEdit ) {
		this.$element.append(
			new OO.ui.MessageWidget( {
				type: 'warning',
				label: mw.message( 'menueditor-ui-redlink-notice' ).text()
			} ).$element
		);
		return;
	}

	// eslint-disable-next-line new-cap
	this.tree = new classname( {
		data: treeData || cfg.defaultData || [],
		editable: this.allowEdit
	} );

	if ( this.allowEdit ) {
		mw.loader.using( [ 'ext.menuEditor.toolbar' ] ).done( () => {
			const menuToolbar = new ext.menueditor.ui.widget.MenuToolbar( {
				toolbarItems: this.toolbarItems
			} );
			this.$element.prepend( menuToolbar.toolbar.$element );

			menuToolbar.toolbar.connect( this, {
				newItem: function () {
					this.tree.addSubnode();
				},
				cancel: function () {
					this.emit( 'cancel' );
				},
				save: 'saveEdit'
			} );
			mw.hook( 'menueditor.toolbar' ).fire( menuToolbar );
		} );
	}

	this.$element.append( this.tree.$element );
};

OO.inheritClass( ext.menueditor.ui.panel.MenuPanel, OO.ui.PanelLayout );

ext.menueditor.ui.panel.MenuPanel.prototype.getTree = function () {
	return this.tree;
};

ext.menueditor.ui.panel.MenuPanel.prototype.saveEdit = function () {
	const data = this.tree.getNodes();

	ext.menueditor.api.save( this.pagename, data ).done( () => {
		this.emit( 'saveSuccess' );
	} ).fail( ( error ) => {
		this.emit( 'saveFailed', error );
	} );
};
