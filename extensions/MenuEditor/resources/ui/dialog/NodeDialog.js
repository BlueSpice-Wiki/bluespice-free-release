ext.menueditor.ui.dialog.NodeDialog = function ( config, node ) {
	ext.menueditor.ui.dialog.NodeDialog.super.call( this, config );
	this.node = node;
	this.allowedNodes = config.allowedNodes || [];
	this.tree = config.tree || {};
	this.form = null;
	this.items = {};
};

OO.inheritClass( ext.menueditor.ui.dialog.NodeDialog, OO.ui.ProcessDialog );

ext.menueditor.ui.dialog.NodeDialog.static.name = 'base-edit-menu-dialog';

ext.menueditor.ui.dialog.NodeDialog.static.title = mw.message( 'menueditor-ui-dialog-title' ).text();

ext.menueditor.ui.dialog.NodeDialog.static.actions = [
	{
		action: 'done',
		label: mw.message( 'menueditor-ui-dialog-action-done' ).text(),
		flags: [ 'primary', 'progressive' ]
	},
	{
		label: mw.message( 'menueditor-ui-dialog-action-cancel' ).text(),
		flags: 'safe'
	}
];

ext.menueditor.ui.dialog.NodeDialog.prototype.initialize = function () {
	ext.menueditor.ui.dialog.NodeDialog.super.prototype.initialize.call( this );
	this.content = new OO.ui.PanelLayout( {
		padded: true,
		expanded: true
	} );
	if ( this.node ) {
		this.pushPending();
		this.node.getForm( this ).done( ( form ) => {
			this.content.$element.append( form.$element );
			this.setForm( form );
		} );
	} else {
		this.actions.setAbilities( { done: false } );
		const selectionItems = this.getAllowedNodeOptions();
		const selector = new OO.ui.DropdownWidget( {
			menu: {
				items: selectionItems
			},
			$overlay: this.$overlay
		} );
		selector.getMenu().connect( this, {
			select: function ( item ) {
				this.setItem( item.getData() );
			}
		} );

		if ( selectionItems.length > 1 ) {
			this.content.$element.append(
				new OO.ui.FieldLayout( selector, {
					label: mw.message( 'menueditor-ui-node-type-selector-label' ).text(),
					align: 'top',
					classes: [ 'menueditor-selector-widget' ]
				} ).$element
			);
		}
		this.formCnt = new OO.ui.PanelLayout( { padded: false, expanded: false } );
		this.content.$element.append( this.formCnt.$element );

		const internal = selector.getMenu().findItemFromData( 'enhanced-sidebar-internal-link' );
		if ( internal ) {
			selector.getMenu().selectItem( internal );
		} else {
			const first = selector.getMenu().findFirstSelectableItem();
			if ( first ) {
				selector.getMenu().selectItem( first );
			}
		}
	}
	this.$body.append( this.content.$element );
};

ext.menueditor.ui.dialog.NodeDialog.prototype.initializeItems = function () {
	for ( let i = 0; i < this.allowedValid.length; i++ ) {
		const type = this.allowedValid[ i ];
		const classname = ext.menueditor.util.callbackFromString(
			ext.menueditor.registry.node.registry[ type ]
		);
		const params = { nodeData: { type: type }, tree: this.tree };
		// eslint-disable-next-line new-cap
		const node = new classname( params );
		if ( !node.shouldRender() ) {
			this.allowedValid.splice( this.allowedValid.indexOf( node ), 2 );
		} else {
			this.items[ type ] = node;
		}
	}

};

ext.menueditor.ui.dialog.NodeDialog.prototype.getAllowedNodeOptions = function () {
	const all = Object.keys( ext.menueditor.registry.node.registry ),
		allowedConfig = this.allowedNodes;
	this.allowedValid = this.allowedNodes.length === 0 ?
		[] : all.filter( ( x ) => allowedConfig.indexOf( x ) >= 0 );

	this.initializeItems();
	return this.allowedValid.map( ( x ) => {
		// The following messages are used here
		// * menueditor-ui-menu-wiki-link-label
		// * menueditor-ui-menu-two-fold-link-spec-label
		// * menueditor-ui-menu-raw-text-label
		// * menueditor-ui-menu-keyword-label
		let msg = mw.message( 'menueditor-ui-' + x + '-label' ),
			label = msg.exists() ? msg.text() : x;

		// Allow other extensions to show a readable name
		// without using message prefix 'menueditor-ui-'
		// eslint-disable-next-line mediawiki/msg-doc
		msg = mw.message( x + '-label' );
		label = msg.exists() ? msg.text() : label;

		return new OO.ui.MenuOptionWidget( {
			data: x,
			label: label
		} );
	} );
};

ext.menueditor.ui.dialog.NodeDialog.prototype.setItem = function ( type ) {
	this.pushPending();

	const node = this.items[ type ];

	node.getForm( this ).done( ( form ) => {
		this.formCnt.$element.html( form.$element );
		this.setForm( form );
	} );
};

ext.menueditor.ui.dialog.NodeDialog.prototype.setForm = function ( form ) {
	this.form = form;

	if ( form ) {
		form.connect( this, {
			renderComplete: function () {
				this.updateSize();
			}
		} );
		this.setSize( 'large' );
		this.actions.setAbilities( { done: true } );
	}

	this.popPending();
	this.updateSize();
};

ext.menueditor.ui.dialog.NodeDialog.prototype.getBodyHeight = function () {
	// eslint-disable-next-line no-jquery/no-class-state
	if ( !this.$errors.hasClass( 'oo-ui-element-hidden' ) ) {
		return this.$element.find( '.oo-ui-processDialog-errors' )[ 0 ].scrollHeight;
	}

	return this.$element.find( '.oo-ui-window-body' )[ 0 ].scrollHeight + 20;
};

ext.menueditor.ui.dialog.NodeDialog.prototype.getActionProcess = function ( action ) {
	if ( action === 'done' ) {
		this.form.connect( this, {
			dataSubmitted: function ( data ) {
				this.close( { action: action, data: data, node: this.node } );
			}
		} );
		this.form.submit();
	}

	return ext.menueditor.ui.dialog.NodeDialog.super.prototype.getActionProcess.call( this, action );
};
